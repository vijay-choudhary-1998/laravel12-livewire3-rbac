<?php

namespace App\Livewire\Permission;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionManager extends Component
{
    use WithPagination;

    public $isEdit = false, $showModal = false;
    public $permissionName, $permissionId, $search;
    protected $listeners = ['delete'];

    public function rules()
    {
        return [
            'permissionName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('Permissions', 'menu')->ignore($this->permissionId),
            ],
        ];
    }
    public function render()
    {
        $permissions = Permission::orderBy('created_at', 'desc');
        if (isset($this->search) && !empty($this->search)) {
            $search = trim($this->search);
            $permissions->where('name', 'LIKE', '%' . $search . '%');
        }
        $permissions = $permissions->where('guard_name', 'web')->paginate(10);
        return view('livewire.permission.permission-manager', compact('permissions'));
    }

    public function create()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate($this->rules());

        $actions = ['show', 'add', 'edit', 'delete'];

        foreach ($actions as $action) {
            Permission::create([
                'name' => $this->permissionName . '-' . $action,
                'menu' => $this->permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Permissions added successfully!']);

        $this->resetForm();
        $this->showModal = false;
    }
    public function edit($id)
    {
        $this->isEdit = true;
        $permission = Permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->permissionName = $permission->name;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate($this->rules());

        Permission::where('name', 'LIKE', $this->permissionName . '-%')->where('guard_name', 'web')->delete();

        $actions = ['add', 'edit', 'show', 'delete'];
        foreach ($actions as $action) {
            Permission::create([
                'name' => $this->permissionName . '-' . $action,
            ]);
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Permissions updated successfully!']);
        $this->resetForm();
        $this->showModal = false;
    }

    public function confirmDelete($id)
    {
        $this->permissionId = $id;
        $this->dispatch('swal:confirm', [
            'title' => 'Are you sure?',
            'text' => 'This action cannot be undone.',
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonText' => 'Yes, delete it!',
            'cancelButtonText' => 'Cancel',
            'action' => 'delete',
        ]);
    }

    public function delete()
    {
        Permission::destroy($this->permissionId);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Staff deleted successfully!']);
    }

    private function resetForm()
    {
        $this->permissionName = '';
    }

    public function updating()
    {
        $this->resetPage();
    }
    public function clearFilter()
    {
        $this->reset('search');
    }
}
