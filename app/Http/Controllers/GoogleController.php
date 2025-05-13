<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        
        // return Socialite::driver('google')->stateless()->redirect();
        return Socialite::driver('google')->redirect(); // ✅ no stateless here

    }

    public function handleGoogleCallback()
    {
        // $googleUser = Socialite::driver('google')->stateless()->user();
        
        $googleUser = Socialite::driver('google')->user();
        
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(uniqid()), // Optional placeholder password
                
                ]
            );

        Auth::login($user);
        session(['recent_login' => true]); // ✅ flag that this is a valid, intentional login


        $token = $user->createToken('google-login')->plainTextToken;

        // return response()->json([
        //     'user' => $user,
        //     'token' => $token
        // ]);


         return redirect()->route('users')->with('token', $token);

    }
}
