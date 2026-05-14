<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Agency Resources') }}
            </h2>
            <a href="{{ route('map') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                ← Back to Live Map
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(!Auth::user()->agency_id)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-medium">You are not associated with any agency.</p>
                    <p class="text-yellow-600 text-sm mt-1">Please register your agency first to manage resources.</p>
                    <a href="{{ route('agency.register') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700">
                        Register Agency
                    </a>
                </div>
            @elseif(Auth::user()->agency && Auth::user()->agency->status !== 'approved')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-blue-800 font-medium">Your agency is pending approval.</p>
                    <p class="text-blue-600 text-sm mt-1">You can manage resources once your agency has been approved by an administrator.</p>
                </div>
            @else
                <livewire:agency-resources />
            @endif

        </div>
    </div>
</x-app-layout>
