<div class="relative flex flex-col" style="margin-left: {{ $level * 40 }}px;" x-data="{ replyOpen: false }">
    
    @if($level > 0)
        <div class="absolute -left-6 top-6 w-6 h-px bg-gray-300"></div>
        <div class="absolute -left-6 -top-6 bottom-6 w-px bg-gray-300"></div>
    @endif

    <div class="bg-white border {{ $email->type == 'outgoing' ? 'border-blue-200 bg-blue-50' : 'border-gray-200' }} rounded-lg shadow-sm p-5 mb-3 hover:shadow-md transition">
        <div class="flex justify-between items-start">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full {{ $email->type == 'outgoing' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold">
                    {{ substr($email->from_name ?? '?', 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-gray-900">
                        {{ $email->from_name }} 
                        @if($email->type == 'outgoing') <span class="text-xs font-normal text-blue-600 bg-blue-100 px-2 py-0.5 rounded ml-2">Sent by you</span> @endif
                    </h4>
                    <p class="text-xs text-gray-500">{{ $email->from_email }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-400 block">{{ \Carbon\Carbon::parse($email->created_at)->diffForHumans() }}</span>
                <button @click="replyOpen = !replyOpen" class="text-xs text-blue-600 hover:underline mt-1 font-medium">Reply ↩</button>
            </div>
        </div>

        <h3 class="mt-3 text-lg font-medium text-gray-800">{{ $email->subject }}</h3>
        
        <div class="mt-2 text-gray-600 text-sm leading-relaxed whitespace-pre-line">
            {{ Str::limit($email->body, 300) }}
        </div>
    </div>

    <div x-show="replyOpen" class="ml-4 mb-4 bg-gray-50 p-4 rounded border border-gray-200" style="display: none;">
        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Replying to {{ $email->from_name }}</h4>
        <form action="{{ route('email.send') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $email->id }}">
            <input type="hidden" name="to_email" value="{{ $email->from_email }}">
            <input type="hidden" name="subject" value="Re: {{ $email->subject }}"> <textarea name="body" rows="3" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Write your reply..."></textarea>
            
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" @click="replyOpen = false" class="text-xs text-gray-500 px-3 py-1">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white text-xs px-4 py-2 rounded hover:bg-blue-700">Send Reply</button>
            </div>
        </form>
    </div>

    @if($email->replies->count() > 0)
        <div class="relative">
             <div class="absolute left-5 top-0 bottom-0 w-px bg-gray-200 -z-10"></div>
             @foreach($email->replies as $reply)
                @include('email-item', ['email' => $reply, 'level' => $level + 1])
             @endforeach
        </div>
    @endif
</div>