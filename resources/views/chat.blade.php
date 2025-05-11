<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with {{ $receiver->name }}</title>
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
                <div class="message {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}">
                    {{ $message->message }}
                    <span class="message-time">
                        {{ $message->created_at->format('h:i A') }}
                        @if($message->sender_id == auth()->id())
                            <i class="fas fa-check-double ms-1"></i>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
        
        <div id="typing-indicator">
            <i class="fas fa-circle" style="font-size: 8px; margin-right: 5px; color: var(--success-color);"></i>
            {{ $receiver->name }} is typing...
        </div>
        
        <form id="message-form" class="message-form">
            @csrf
            <input type="text" id="message-input" class="form-control" placeholder="Type a message..." autocomplete="off">
            <button type="submit" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function (){
            
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
                            // show the received message
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'message received';
                            const now = new Date();
                            const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                            messageDiv.innerHTML = `
                                ${e.message.message}
                                <span class="message-time">${timeString} <i class="fas fa-check-double ms-1"></i></span>
                            `;
                            chatBox.appendChild(messageDiv);
                            chatBox.scrollTop = chatBox.scrollHeight;
                        });


            // subscribe to typing channel
            window.Echo.private('typing.' + receiverId)
                        .listen('UserTyping', (e) => {
                            if(e.typerId === receiverId){
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
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                    messageDiv.innerHTML = `
                        ${message}
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
                typingTimeOut = setTimeout(() => {typingIndicator.style.display = 'none'}, 3000);
            });

            // Set user offline on window close
            window.addEventListener('beforeunload', function () {
                fetch('/offline', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            });

        });
    </script>
</body>
</html>