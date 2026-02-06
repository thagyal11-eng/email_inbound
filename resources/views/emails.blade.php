<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox & Compose</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen p-10" x-data="{ composeOpen: false }">

    @if(session('success'))
        <div class="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Inbox</h1>
            <button @click="composeOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow font-medium flex items-center gap-2">
                <span>+</span> Compose New
            </button>
        </div>

        @foreach($emails as $email)
            <div class="mb-8">
                @include('email-item', ['email' => $email, 'level' => 0])
            </div>
        @endforeach

        @if($emails->isEmpty())
            <div class="text-center text-gray-500 mt-10">No emails found.</div>
        @endif
    </div>

    <div x-show="composeOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 relative">
            <button @click="composeOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
            
            <h2 class="text-xl font-bold mb-4">New Email</h2>
            
            <form action="{{ route('email.send') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">To:</label>
                    <input type="email" name="to_email" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500" required placeholder="client@example.com">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject:</label>
                    <input type="text" name="subject" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500" required placeholder="Meeting request">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message:</label>
                    <textarea name="body" rows="4" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="composeOpen = false" class="text-gray-500 hover:text-gray-700 px-4 py-2">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Send Email</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>