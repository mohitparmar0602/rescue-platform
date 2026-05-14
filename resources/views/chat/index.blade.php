<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Secure Messaging
            </h2>
            <span class="text-xs text-gray-500 flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                AES-256 Encrypted
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(!Auth::user()->agency_id)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-medium">You must belong to an approved agency to use messaging.</p>
                    <a href="{{ route('agency.register') }}"
                       class="mt-3 inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700">
                        Register Agency
                    </a>
                </div>
            @elseif(!Auth::user()->agency || Auth::user()->agency->status !== 'approved')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-blue-800 font-medium">Your agency is pending admin approval.</p>
                    <p class="text-blue-600 text-sm mt-1">Messaging will be available once approved.</p>
                </div>
            @else
                <livewire:agency-chat :withAgency="request()->integer('with') ?: null" />
            @endif

        </div>
    </div>
</x-app-layout>
