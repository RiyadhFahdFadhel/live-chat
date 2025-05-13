<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageEdited;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Events\MessageRead;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get(); //  Exclude self
        return view('users', compact('users'));
    }

    public function chat($receiverId)
    {
        $receiver = User::find($receiverId);

        if ($receiverId == Auth::id()) {
            abort(403, "You can't chat with yourself.");
        }


        //  Clean dual-query using OR and order

        $messages = Message::where(function ($query) use ($receiverId) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', Auth::id());
        })

            //  Only non-deleted messages
            ->whereNull('deleted_at')

            ->orderBy('created_at')->get();

        return view('chat', compact('receiver', 'messages'));
    }

    public function sendMessage(Request $request, $receiverId)
    {
        $request->validate([
            'message' => 'required|string|max:1000' //  Basic input validation
        ]);

        // save message to DB
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $request['message']
        ]);

        // Fire the message event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message sent!']);
    }

    public function typing()
    {
        // Fire the typing event
        broadcast(new UserTyping(Auth::id()))->toOthers();
        return response()->json(['status' => 'typing broadcasted!']);
    }

    public function setOnline()
    {
        //  Caching for online status
        Cache::put('user-is-online-' . Auth::id(), true, now()->addMinutes(5));
        return response()->json(['status' => 'Online']);
    }

    public function setOffline()
    {
        Cache::forget('user-is-online-' . Auth::id());
        return response()->json(['status' => 'Offline']);
    }


    public function destroy($id)
    {
        $message = Message::findOrFail($id);

        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Soft delete assumed
        $messageId = $message->id;
        $message->delete();

        try {
            broadcast(new MessageDeleted($message))->toOthers();
        } catch (\Throwable $e) {
            Log::error('Broadcast delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        return response()->json(['message_id' => $messageId]);
    }

    public function update(Request $request, $id)
    {
        Log::info('Edit message debug', [
            'auth_id' => Auth::id(),
            'sender_id' => Message::find($id)?->sender_id,
            'message_id' => $id
        ]);
        $message = Message::findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);


        // if ($message->sender_id !== Auth::id()) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $message->message = $request->message;
        $message->save();

        $message->refresh(); // ⬅️ Important!


        broadcast(new MessageEdited($message))->toOthers();
        Log::info('Broadcasting edit', ['channel' => 'chat.' . $message->receiver_id]);


        return response()->json(['message' => $message]);
    }

    public function markAsRead(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        // Only receiver can mark message as read
        // if ($message->receiver_id !== Auth::id()) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $message->read_at = now();
        $message->save();

        $message->refresh(); // ⬅️ Important!


        broadcast(new MessageRead($message))->toOthers();

        return response()->json(['status' => 'Read']);
    }



}
