<div class="bg-white shadow sm:rounded-lg overflow-hidden mt-6">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Agency Resources
        </h3>
        <button wire:click="toggleForm" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ $showForm ? 'Cancel' : 'Add Resource' }}
        </button>
    </div>

    @if($showForm)
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <form wire:submit.prevent="saveResource" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select wire:model="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="vehicle">Vehicle</option>
                        <option value="personnel">Personnel</option>
                        <option value="equipment">Equipment</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g. Ambulance">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" wire:model="quantity" min="1" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="available">Available</option>
                        <option value="deployed">Deployed</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-5 flex justify-end mt-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        {{ $editMode ? 'Update Resource' : 'Save Resource' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($resources as $resource)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $resource->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $resource->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $resource->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($resource->status === 'available')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Available</span>
                            @elseif($resource->status === 'deployed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Deployed</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Maintenance</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="editResource({{ $resource->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button wire:click="deleteResource({{ $resource->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No resources added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
