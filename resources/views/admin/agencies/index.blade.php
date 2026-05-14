<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pending Agency Approvals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-4 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-4 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($pendingAgencies->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-gray-500">No pending agencies to approve.</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pendingAgencies as $agency)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $agency->name }}</h3>
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 mt-1">
                                            {{ ucfirst($agency->type) }}
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                        Pending
                                    </span>
                                </div>
                                
                                <div class="mt-4 space-y-2 text-sm text-gray-600">
                                    <p><strong class="text-gray-900">Registration No:</strong> {{ $agency->registration_no }}</p>
                                    <p><strong class="text-gray-900">Address:</strong> {{ $agency->address }}</p>
                                </div>

                                @if($agency->documents->isNotEmpty())
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">Documents:</h4>
                                        <ul class="space-y-1">
                                            @foreach($agency->documents as $doc)
                                                <li>
                                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                        View Proof of Registration
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                                <form method="POST" action="{{ route('admin.agencies.approve', $agency) }}" onsubmit="return confirm('Are you sure you want to approve this agency?');">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Approve Agency
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
