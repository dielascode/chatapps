<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Realtime Chat - Laravel Reverb</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 30px 15px;
        }

        .chat-container {
            max-width: 750px;
            margin: auto;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, .08);
        }

        .chat-header {
            background: #2563eb;
            color: white;
            padding: 20px;
        }

        .chat-header h2 {
            margin: 0 0 8px;
        }

        .status {
            font-size: 13px;
            color: #dbeafe;
        }

        .chat-body {
            height: 420px;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .message.mine {
            align-items: flex-end;
        }

        .message.other {
            align-items: flex-start;
        }

        .message-info {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .message-content {
            max-width: 75%;
            padding: 12px 15px;
            border-radius: 12px;
            background: #e2e8f0;
            overflow-wrap: anywhere;
        }

        .message.mine .message-content {
            background: #2563eb;
            color: white;
        }

        .message-time {
            font-size: 10px;
            margin-top: 5px;
            color: #94a3b8;
        }

        .chat-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .name-input,
        .message-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .send-area {
            display: flex;
            gap: 10px;
        }

        .send-area input {
            flex: 1;
            margin: 0;
        }

        button {
            border: none;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        @media(max-width: 500px) {
            .chat-body {
                height: 400px;
            }

            .message-content {
                max-width: 90%;
            }
        }
    </style>
</head>

<body>

    <div class="chat-container">

        <div class="chat-header">

            <div style="display:flex; justify-content:space-between; align-items:center;">

                <div>
                    <h2>Realtime Chat</h2>

                    <div class="status" id="connection-status">
                        Menghubungkan ke server...
                    </div>
                </div>

                <div style="display:flex; gap:8px;">

                    <button type="button" onclick="changeUser()" style="background:#1e40af;">
                        Ganti User
                    </button>

                    <button type="button" onclick="deleteAllChat()" style="background:#dc2626;">
                        Hapus Chat
                    </button>

                </div>

            </div>

        </div>

        <div class="chat-body" id="chat-box">
            <p style="text-align:center;color:#94a3b8;">
                Memuat percakapan...
            </p>
        </div>

        <div class="chat-footer">

            <input type="text" id="sender" class="name-input" placeholder="Masukkan nama kamu..." maxlength="50">

            <form id="chat-form">

                <div class="send-area">

                    <input type="text" id="message" class="message-input" placeholder="Ketik pesan..."
                        maxlength="2000" required>

                    <button type="submit" id="send-button">
                        Kirim
                    </button>

                </div>

            </form>

        </div>

    </div>

    <script type="module">
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');

        const senderInput = document.getElementById('sender');
        const messageInput = document.getElementById('message');

        const sendButton = document.getElementById('send-button');
        const statusElement = document.getElementById('connection-status');

        // Membuat identitas pengguna per browser
        let sender = sessionStorage.getItem('chat_sender');

        if (sender) {
            senderInput.value = sender;
        }

        senderInput.addEventListener('change', function() {
            sender = this.value.trim();

            if (sender) {
                sessionStorage.setItem('chat_sender', sender);
            }
        });
        window.deleteAllChat = async function() {

            const confirmation = confirm(
                'Apakah kamu yakin ingin menghapus seluruh chat?'
            );

            if (!confirmation) {
                return;
            }

            try {

                const response = await fetch('/chat/delete-all', {

                    method: 'DELETE',

                    headers: {
                        'Accept': 'application/json',

                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                    }

                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Gagal menghapus chat.');
                }

                // Mengosongkan tampilan chat
                chatBox.innerHTML = `
            <p class="empty-message"
               style="text-align:center;color:#94a3b8;">
                Belum ada percakapan.
            </p>
        `;

                alert(result.message);

            } catch (error) {

                console.error(error);
                alert(error.message);

            }

        };
        window.changeUser = function() {

            const newName = prompt(
                'Masukkan nama pengguna baru:',
                sender
            );

            if (!newName || !newName.trim()) {
                return;
            }

            sender = newName.trim();

            senderInput.value = sender;

            sessionStorage.setItem('chat_sender', sender);

            alert('Identitas berhasil diganti menjadi ' + sender);
        };
        // Menghindari HTML injection saat menampilkan pesan
        function addMessage(data) {

            const emptyMessage = chatBox.querySelector('.empty-message');

            if (emptyMessage) {
                emptyMessage.remove();
            }

            const messageElement = document.createElement('div');

            const isMine = data.sender === sender;

            messageElement.className = isMine ?
                'message mine' :
                'message other';

            const info = document.createElement('div');
            info.className = 'message-info';
            info.textContent = data.sender;

            const content = document.createElement('div');
            content.className = 'message-content';
            content.textContent = data.message;

            const time = document.createElement('div');
            time.className = 'message-time';
            time.textContent = data.created_at;

            messageElement.appendChild(info);
            messageElement.appendChild(content);
            messageElement.appendChild(time);

            chatBox.appendChild(messageElement);

            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Mengambil riwayat pesan dari database
        async function loadMessages() {

            try {

                const response = await fetch('/chat/messages');

                if (!response.ok) {
                    throw new Error('Gagal mengambil riwayat pesan.');
                }

                const messages = await response.json();

                chatBox.innerHTML = '';

                if (messages.length === 0) {
                    chatBox.innerHTML = `
                    <p class="empty-message"
                       style="text-align:center;color:#94a3b8;">
                        Belum ada percakapan.
                    </p>
                `;
                }

                messages.forEach(message => {
                    addMessage(message);
                });

            } catch (error) {

                console.error(error);
                chatBox.textContent = 'Gagal memuat percakapan.';

            }
        }

        // Mengirim pesan ke Laravel
        chatForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            sender = senderInput.value.trim();

            const message = messageInput.value.trim();

            if (!sender) {
                alert('Masukkan nama terlebih dahulu!');
                senderInput.focus();
                return;
            }

            if (!message) {
                return;
            }

            sessionStorage.setItem('chat_sender', sender);

            sendButton.disabled = true;

            try {

                const response = await fetch('/chat/send', {

                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',

                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content
                    },

                    body: JSON.stringify({
                        sender: sender,
                        message: message
                    })

                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'Gagal mengirim pesan.'
                    );
                }

                messageInput.value = '';
                messageInput.focus();

                // Pesan akan ditampilkan melalui event broadcast.
                // Tidak perlu menambahkan pesan secara manual di sini.

            } catch (error) {

                console.error(error);
                alert(error.message);

            } finally {

                sendButton.disabled = false;

            }

        });

        // Mendengarkan koneksi Reverb
        if (!window.Echo) {

            statusElement.textContent =
                'Echo belum berhasil dikonfigurasi.';

            throw new Error('Laravel Echo tidak ditemukan.');

        }

        // Memantau status koneksi WebSocket
        window.Echo.connector.pusher.connection.bind(
            'connected',
            () => {
                statusElement.textContent =
                    'Terhubung ke server real-time';
            }
        );

        window.Echo.connector.pusher.connection.bind(
            'disconnected',
            () => {
                statusElement.textContent =
                    'Koneksi terputus';
            }
        );

        // Berlangganan channel chat
        window.Echo.channel('chat')
            .listen('MessageSent', (event) => {

                console.log('Pesan diterima:', event);

                addMessage(event);

            });

        // Memuat riwayat pesan
        loadMessages();
    </script>

</body>

</html>
