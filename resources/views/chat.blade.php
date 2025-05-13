<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with {{ $receiver->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --muted-color: #6c757d;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .chat-container {
            max-width: 900px;
            margin: 2rem auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: bold;
        }

        .online-status {
            font-size: 0.8rem;
            color: #a5d8ff;
        }

        #chat-box {
            height: 500px;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 15px;
            position: relative;
            word-wrap: break-word;
        }

        .received {
            align-self: flex-start;
            background-color: white;
            border-bottom-left-radius: 5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .sent {
            align-self: flex-end;
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.8;
            margin-top: 0.25rem;
            display: block;
            text-align: right;
        }

        .sent .message-time {
            color: rgba(255, 255, 255, 0.8);
        }

        #typing-indicator {
            padding: 0.5rem 1.5rem;
            font-style: italic;
            color: var(--muted-color);
            background-color: white;
            display: none;
        }

        .message-form {
            display: flex;
            padding: 1rem;
            background-color: white;
            border-top: 1px solid #e9ecef;
        }

        #message-input {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 25px;
            padding: 0.75rem 1.25rem;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }

        #message-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        .send-button {
            background: var(--primary-color);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .send-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #b8c2d1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a3a9b5;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .chat-container {
                margin: 0;
                border-radius: 0;
                height: 100vh;
            }

            #chat-box {
                height: calc(100vh - 180px);
            }

            .message:hover .edit-btn,
            .message:hover .delete-btn {
                display: inline-block;
            }

            .edit-btn,
            .delete-btn {
                display: none;
                background: none;
                border: none;
                cursor: pointer;
                margin-left: 8px;
            }

        }

        .action-button {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-left: 5px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .action-button:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }
    </style>
    @vite(['resources/js/app.js'])
</head>

<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr($receiver->name, 0, 1)) }}</div>
                <div>
                    <h5 class="mb-0">{{ $receiver->name }}</h5>
                    <div class="online-status" id="user-status">
                        <i class="fas fa-circle" style="font-size: 8px; margin-right: 5px;"></i>
                        Online
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-ellipsis-v" style="cursor: pointer;"></i>
            </div>
        </div>

        <div id="chat-box">
            @foreach ($messages as $message)
                <div class="message {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}"
                    id="msg-{{ $message->id }}">
                    <span class="message-text">{{ $message->message }}</span>

                    <span class="message-time">
                        {{ $message->created_at->format('h:i A') }}
                        @if($message->sender_id == auth()->id())
                            <i class="fas fa-check-double ms-1"></i>
                        @endif
                    </span>

                    @if($message->sender_id == auth()->id())
                        <button class="action-button edit-btn" data-id="{{ $message->id }}" aria-label="Edit">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="action-button delete-btn" data-id="{{ $message->id }}" aria-label="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>


        <div id="typing-indicator">
            <i class="fas fa-circle" style="font-size: 8px; margin-right: 5px; color: var(--success-color);"></i>
            {{ $receiver->name }} is typing...
        </div>

        <form id="message-form" class="message-form">
            @csrf
            <input type="text" id="message-input" class="form-control" placeholder="Type a message..."
                autocomplete="off">
            <button type="submit" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let receiverId = {{ $receiver->id }};
            let senderId = {{ auth()->id() }};
            let chatBox = document.getElementById('chat-box');
            let messageForm = document.getElementById('message-form');
            let messageInput = document.getElementById('message-input');
            let typingIndicator = document.getElementById('typing-indicator');

            // Set user online
            fetch('/online',
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    }
                }
            );


            // subscribe to chat channel
            window.Echo.private('chat.' + senderId)
                .listen('MessageSent', (e) => {
                    const isOwn = e.message.sender_id === senderId;
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message ' + (isOwn ? 'sent' : 'received');
                    messageDiv.id = 'msg-' + e.message.id;

                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    messageDiv.innerHTML = `
        <span class="message-text">${e.message.message}</span>
        <span class="message-time">${timeString} ${isOwn ? '<i class="fas fa-check-double ms-1"></i>' : ''}</span>
        ${isOwn ? `
            <button class="action-button edit-btn" data-id="${e.message.id}" aria-label="Edit">
                <i class="fas fa-pen"></i>
            </button>
            <button class="action-button delete-btn" data-id="${e.message.id}" aria-label="Delete">
                <i class="fas fa-trash"></i>
            </button>
        ` : ''}
    `;

                    chatBox.appendChild(messageDiv);
                    chatBox.scrollTop = chatBox.scrollHeight;
                })


                // the user can delete his own messages
                .listen('MessageEdited', (e) => {
                    const el = document.querySelector(`#msg-${e.id} .message-text`);
                    console.log('[MessageEdited]', e);

                    if (el) {
                        el.textContent = e.message;

                        // Optional: show "edited" indicator
                        const time = el.nextElementSibling;
                        if (time && !time.innerText.includes('(edited)')) {
                            time.innerText += ' (edited)';
                        }
                    }
                })
                .listen('MessageDeleted', (e) => {
                    const el = document.getElementById(`msg-${e.message_id}`);
                    if (el) el.remove();
                });




            // subscribe to typing channel
            window.Echo.private('typing.' + receiverId)
                .listen('UserTyping', (e) => {
                    if (e.typerId === receiverId) {
                        typingIndicator.style.display = 'block';
                        setTimeout(() => typingIndicator.style.display = 'none', 3000);
                    }
                });



            messageForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const message = messageInput.value;
                if (message) {
                    fetch(`/chat/${receiverId}/send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message })
                    });

                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message sent';
                    messageDiv.id = 'msg-temp-' + Date.now(); // temporary ID

                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    messageDiv.innerHTML = `
            <span class="message-text">${message}</span>
            <span class="message-time">${timeString} <i class="fas fa-check-double ms-1"></i></span>
        `;

                    chatBox.appendChild(messageDiv);
                    chatBox.scrollTop = chatBox.scrollHeight;
                    messageInput.value = '';
                }
            });
            let typingTimeOut;
            messageInput.addEventListener('input', function () {
                clearTimeout(typingTimeOut);
                fetch(`/chat/typing`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                typingTimeOut = setTimeout(() => { typingIndicator.style.display = 'none' }, 3000);
            });

            // Set user offline on window close
            window.addEventListener('beforeunload', function () {
                fetch('/offline', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            });


        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ... your existing code ...

            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // ✅ Event delegation for edit/delete buttons
            document.getElementById('chat-box').addEventListener('click', function (e) {
                const btn = e.target.closest('button');
                if (!btn || !btn.dataset.id) return;

                const id = btn.dataset.id;

                if (btn.classList.contains('delete-btn')) {
                    if (!confirm("Delete this message?")) return;

                    fetch(`/chat/destroy/${id}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    })
                        .then(() => {
                            const el = document.getElementById(`msg-${id}`);
                            if (el) el.remove();
                        })
                        .catch(() => alert('Could not delete message.'));
                }

                if (btn.classList.contains('edit-btn')) {
                    const newText = prompt("Edit your message:");
                    if (!newText) return;

                    fetch(`/chat/update/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ message: newText })
                    })
                        .then(res => res.json())
                        .then(res => {
                            const el = document.querySelector(`#msg-${id} .message-text`);
                            if (el) el.textContent = res.message.message;
                        })
                        .catch(() => alert('Could not edit message.'));
                }
            });

            // ✅ remove the old global functions (optional cleanup)
        });

    </script>
</body>

</html>