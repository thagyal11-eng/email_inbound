<div class="relative flex flex-col transition-all duration-300 ease-in-out" 
     style="padding-left: {{ $level > 0 ? '3rem' : '0' }};" 
     x-data="{ replyOpen: false }">
    
    @if($level > 0)
        <div class="absolute left-[1.65rem] -top-4 bottom-0 w-[2px] bg-gray-300"></div>
        <div class="absolute left-[1.65rem] top-8 w-6 h-[2px] bg-gray-300"></div>
    @endif

    <div class="relative group z-10 mb-4">
        
        <div class="flex gap-4">
            <div class="flex-shrink-0 relative">
                <div class="h-10 w-10 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-sm font-bold
                    {{ $email->type == 'outgoing' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                    {{ substr($email->from_name ?? '?', 0, 1) }}
                </div>
                @if($email->type == 'outgoing')
                    <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm border border-gray-100">
                        <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="bg-white border {{ $email->type == 'outgoing' ? 'border-indigo-100 bg-indigo-50/30' : 'border-gray-200' }} rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ $email->from_name }}
                                </span>
                                <span class="text-xs text-gray-400 font-normal">
                                    &lt;{{ $email->from_email }}&gt;
                                </span>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-800 mt-1 {{ $level > 0 ? 'text-gray-500 font-normal' : '' }}">
                                {{ $email->subject }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-3 ml-4">
                            <span class="text-xs text-gray-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($email->created_at)->shortRelativeDiffForHumans() }}
                            </span>
                            <button @click="replyOpen = !replyOpen" class="text-gray-400 hover:text-indigo-600 transition-colors p-1 rounded-full hover:bg-gray-100/50 focus:outline-none focus:ring-2 focus:ring-indigo-500" title="Reply">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-reply"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 leading-relaxed break-words" x-data="{ expanded: false }">
                        
                        <div class="font-medium text-gray-800 [&>p]:mb-2 [&>ul]:list-disc [&>ul]:ml-4">
                            {!! $email->clean_body !!}
                        </div>

                        @if($email->hasHistory())
                            <div class="mt-2">
                                <button @click="expanded = !expanded" 
                                        class="flex items-center gap-1 text-xs font-semibold text-gray-400 bg-gray-50 hover:bg-gray-100 hover:text-gray-600 px-2 py-1 rounded border border-gray-200 transition-colors"
                                        title="Toggle quoted text">
                                    <span x-text="expanded ? 'Hide quoted text' : '•••'"></span>
                                </button>
                                
                                <div x-show="expanded" x-collapse class="mt-2 pl-3 border-l-2 border-gray-300 text-gray-400 text-xs opacity-75">
                                    {!! str_replace($email->clean_body, '', $email->body) !!}
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div x-show="replyOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="ml-14 mb-6 z-20 relative">
        
        <div class="bg-white rounded-xl border border-indigo-100 shadow-lg p-4 relative ring-4 ring-indigo-50">
            <div class="absolute -top-2 left-6 w-4 h-4 bg-white border-t border-l border-indigo-100 transform rotate-45"></div>

            <form action="{{ route('email.send') }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $email->id }}">
                <input type="hidden" name="to_email" value="{{ $email->from_email }}">
                <input type="hidden" name="subject" value="Re: {{ $email->subject }}">

                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Replying to {{ explode(' ', $email->from_name)[0] }}</span>
                    <button type="button" @click="replyOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <textarea name="body" rows="3" class="w-full bg-gray-50 border-0 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 resize-none" placeholder="Type your message here..." required autofocus></textarea>
                
                <div class="flex justify-end gap-3 mt-3">
                    <button type="submit" class="bg-indigo-600 text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm">
                        <span>Send Reply</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($email->replies->count() > 0)
        <div class="relative">
             @foreach($email->replies as $reply)
                @include('email-item', ['email' => $reply, 'level' => $level + 1])
             @endforeach
        </div>
    @endif
</div>