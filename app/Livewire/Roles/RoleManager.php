<?php
namespace App\Livewire\Roles;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManager extends Component
{
    use WithPagination;

    public $isEdit = false, $showModal = false;
    public $roleName, $roleId, $user;
    public $permissions = [];
    public $menus = [];
    protected $listeners = ['delete'];
    public function rules()
    {
        return [
            'roleName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->roleId),
            ],
        ];
    }
    public function mount()
    {
        $this->user = Auth::guard('web')->user();

        $permission = Permission::orderBy('menu', 'asc')->where('guard_name', 'web')->get();
        foreach ($permission as $value) {
            $this->menus[$value->menu][] = $value->toArray();
        }
    }

    public function render()
    {
        $roles = Role::with('permissions')->orderBy('created_at', 'desc')->where('guard_name', 'web')->paginate(10);
        return view('livewire.roles.role-manager', compact('roles'));
    }

    public function toggleAllPermissions($checked = true)
    {
        $this->permissions = $checked
            ? Permission::pluck('name')->toArray()
            : [];
    }

    public function toggleMenuPermissions($menu, $checked)
    {
        $menuPermissions = collect($this->menus[$menu])->pluck('name')->toArray();

        if ($checked) {
            $this->permissions = array_unique(array_merge($this->permissions, $menuPermissions));
        } else {
            $this->permissions = array_diff($this->permissions, $menuPermissions);
        }
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

        if (empty($this->permissions)) {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Please select at least one permission.']);
            return;
        }
        $role = Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
        $role->syncPermissions($this->permissions);

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Role added successfully!']);
        $this->resetForm();
        $this->showModal = false;
    }


    public function edit($id)
    {
        $this->isEdit = true;
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;

        $this->permissions = $role->permissions->pluck('name')->toArray();

        $this->showModal = true;
    }


    public function update()
    {
        $this->validate($this->rules());


        if (empty($this->permissions)) {
            $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Please select at least one permission.']);
            return;
        }

        $role = Role::findOrFail($this->roleId);
        $role->update([
            'name' => $this->roleName,
        ]);
        $role->syncPermissions($this->permissions);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Role updated successfully!']);
        $this->resetForm();
        $this->showModal = false;
    }

    public function confirmDelete($id)
    {
        if ($id == 30) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'You do not have the right permission to delete this.']);
            return;
        }

        $this->roleId = $id;
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
        Role::destroy($this->roleId);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Role deleted successfully!']);
    }

    private function resetForm()
    {
        $this->reset('roleName','permissions');
    }

}
