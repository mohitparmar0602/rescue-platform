<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Resource;
use App\Events\ResourceStatusChanged;
use Illuminate\Support\Facades\Auth;

class AgencyResources extends Component
{
    public $resources;
    public $showForm = false;
    public $editMode = false;
    
    public $resourceId;
    public $type = 'vehicle';
    public $name;
    public $quantity = 1;
    public $status = 'available';

    protected $rules = [
        'type' => 'required|in:vehicle,personnel,equipment',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'status' => 'required|in:available,deployed,maintenance',
    ];

    public function mount()
    {
        $this->loadResources();
    }

    public function loadResources()
    {
        $agency = Auth::user()->agency;
        if ($agency) {
            $this->resources = $agency->resources()->get();
        } else {
            $this->resources = collect();
        }
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function editResource($id)
    {
        $resource = Resource::findOrFail($id);
        
        if ($resource->agency_id !== Auth::user()->agency_id) {
            abort(403);
        }

        $this->resourceId = $resource->id;
        $this->type = $resource->type;
        $this->name = $resource->name;
        $this->quantity = $resource->quantity;
        $this->status = $resource->status;
        
        $this->editMode = true;
        $this->showForm = true;
    }

    public function saveResource()
    {
        $this->validate();
        
        $agency = Auth::user()->agency;
        if (!$agency) return;

        if ($this->editMode) {
            $resource = Resource::findOrFail($this->resourceId);
            if ($resource->agency_id === $agency->id) {
                $resource->update([
                    'type'     => $this->type,
                    'name'     => $this->name,
                    'quantity' => $this->quantity,
                    'status'   => $this->status,
                ]);
            }
        } else {
            $agency->resources()->create([
                'type'     => $this->type,
                'name'     => $this->name,
                'quantity' => $this->quantity,
                'status'   => $this->status,
            ]);
        }

        $this->resetForm();
        $this->loadResources(); // reload first

        broadcast(new ResourceStatusChanged(
            $agency->id,
            $this->resources->map(fn($r) => [
                'name'     => $r->name,
                'quantity' => $r->quantity,
                'status'   => $r->status,
            ])->values()->toArray()
        ));
    }

    public function deleteResource($id)
    {
        $resource = Resource::findOrFail($id);
        $agency   = Auth::user()->agency;

        if ($resource->agency_id === $agency->id) {
            $resource->delete();
            $this->loadResources(); // reload first

            broadcast(new ResourceStatusChanged(
                $agency->id,
                $this->resources->map(fn($r) => [
                    'name'     => $r->name,
                    'quantity' => $r->quantity,
                    'status'   => $r->status,
                ])->values()->toArray()
            ));
        }
    }

    public function resetForm()
    {
        $this->resourceId = null;
        $this->type = 'vehicle';
        $this->name = '';
        $this->quantity = 1;
        $this->status = 'available';
        $this->editMode = false;
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.agency-resources');
    }
}
