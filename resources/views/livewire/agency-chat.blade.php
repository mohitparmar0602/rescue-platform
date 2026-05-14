<div
    class="flex h-[calc(100vh-160px)] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    x-data="{
        init() {
            if (typeof window.Echo === 'undefined') return;
            const agencyId = {{ Auth::user()->agency_id ?? 'null' }};
            if (!agencyId) return;

            window.Echo.channel(`agency.${agencyId}.chat`)
                .listen('NewMessageReceived', (e) => {
                    // Tell Livewire a message came in from a specific agency
                    $wire.refreshOnIncoming(e.senderAgencyId);
                });
        }
    }"
>
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR — conversation list                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <aside class="w-72 shrink-0 border-r border-gray-200 flex flex-col bg-gray-50">

        {{-- Header --}}
        <div class="px-4 py-4 border-b border-gray-200 bg-white">
            <h2 class="text-base font-bold text-gray-900">💬 Messages</h2>
            <p class="text-xs text-gray-500 mt-0.5">Agency-to-agency communications</p>
        </div>

        {{-- New conversation picker --}}
        <div class="px-3 py-3 border-b border-gray-200 bg-white">
            <select
                wire:change="openConversation($event.target.value)"
                class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            >
                <option value="">＋ New conversation…</option>
                @foreach(App\Models\Agency::where('status', 'approved')
                    ->where('id', '!=', Auth::user()->agency_id ?? 0)
                    ->orderBy('name')->get() as $ag)
                    <option value="{{ $ag->id }}">{{ $ag->name }} ({{ $ag->type }})</option>
                @endforeach
            </select>
        </div>

        {{-- Conversation list --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
            @forelse($sidebar as $conv)
                <button
                    wire:click="openConversation({{ $conv['agency_id'] }})"
                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition
                           {{ $activeConversationAgencyId === $conv['agency_id'] ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }}"
                >
                    <div class="flex justify-between items-center mb-0.5">
                        <span class="text-sm font-semibold text-gray-900 truncate">
                            {{ $conv['agency_name'] }}
                        </span>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($conv['unread'] > 0)
                                <span class="inline-flex items-center justify-center h-5 min-w-5 px-1.5
                                             rounded-full bg-indigo-600 text-white text-xs font-bold">
                                    {{ $conv['unread'] }}
                                </span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $conv['last_at'] }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $conv['last_preview'] ?: '…' }}</p>
                    <span class="inline-block mt-1 text-xs capitalize text-gray-400">{{ $conv['agency_type'] }}</span>
                </button>
            @empty
                <div class="px-4 py-8 text-center text-gray-400 text-sm">
                    <p class="text-2xl mb-2">📭</p>
                    No conversations yet.<br>Start one using the dropdown above.
                </div>
            @endforelse
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- MAIN — message thread                                              --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        @if($activeConversationAgencyId)
            @php
                $partner = App\Models\Agency::find($activeConversationAgencyId);
            @endphp

            {{-- Thread header --}}
            <div class="px-5 py-3.5 border-b border-gray-200 bg-white flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($partner?->name ?? '?', 0, 2)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">{{ $partner?->name ?? 'Unknown Agency' }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ $partner?->type }} · encrypted end-to-end 🔒</p>
                </div>
            </div>

            {{-- Messages --}}
            <div
                class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-gray-50"
                id="chat-thread"
                x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
            >
                @php $lastDate = null; @endphp
                @forelse($messages as $msg)
                    {{-- Date separator --}}
                    @if($lastDate !== $msg['sent_date'])
                        <div class="flex items-center gap-3 my-2">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium">{{ $msg['sent_date'] }}</span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                        @php $lastDate = $msg['sent_date']; @endphp
                    @endif

                    {{-- Message bubble --}}
                    <div class="flex {{ $msg['mine'] ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[72%]">
                            @if(!$msg['mine'])
                                <p class="text-xs text-gray-500 mb-1 ml-1">{{ $msg['sender_name'] }}</p>
                            @endif
                            <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed shadow-sm
                                {{ $msg['mine']
                                    ? 'bg-indigo-600 text-white rounded-br-sm'
                                    : 'bg-white text-gray-900 border border-gray-200 rounded-bl-sm' }}">
                                {{ $msg['body'] }}
                            </div>
                            <p class="text-xs text-gray-400 mt-1 {{ $msg['mine'] ? 'text-right mr-1' : 'ml-1' }}">
                                {{ $msg['sent_at'] }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-400 py-16">
                        <p class="text-4xl mb-3">🔒</p>
                        <p class="font-medium text-gray-600">Start the conversation</p>
                        <p class="text-sm mt-1">All messages are encrypted with Laravel's AES-256 encryption.</p>
                    </div>
                @endforelse
            </div>

            {{-- Compose bar --}}
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                <form wire:submit.prevent="send" class="flex gap-2 items-end">
                    <div class="flex-1 relative">
                        <textarea
                            wire:model.live.debounce.300ms="newMessage"
                            placeholder="Type a message…"
                            rows="1"
                            id="chat-input"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm resize-none
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none
                                   max-h-32 overflow-y-auto"
                            style="field-sizing: content"
                            onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); this.closest('form').dispatchEvent(new Event('submit', {bubbles:true})); }"
                        ></textarea>
                        <span class="absolute bottom-2.5 right-3 text-xs text-gray-400">
                            {{ mb_strlen($newMessage) }}/2000
                        </span>
                    </div>
                    <button
                        type="submit"
                        class="shrink-0 h-10 w-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white
                               flex items-center justify-center transition shadow-sm disabled:opacity-50"
                        :disabled="{{ $newMessage === '' ? 'true' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </form>
                <p class="text-xs text-gray-400 mt-1.5 pl-1">
                    🔒 Encrypted with AES-256 · Enter to send · Shift+Enter for new line
                </p>
            </div>

        @else
            {{-- Empty state --}}
            <div class="flex-1 flex flex-col items-center justify-center text-center text-gray-400 px-8">
                <p class="text-6xl mb-4">💬</p>
                <h3 class="text-lg font-semibold text-gray-700">Select a conversation</h3>
                <p class="text-sm mt-2 max-w-xs">
                    Choose an existing thread from the sidebar, or start a new conversation
                    with another approved agency using the dropdown above.
                </p>
                <p class="text-xs mt-4 text-gray-300">🔒 All messages encrypted with AES-256</p>
            </div>
        @endif
    </div>
</div>
