<div class="container">
    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="breadcrumb-title pe-2" style="border: none;">Role</div>
            <div class="pe-3">
                @can('roles-add')
                    <a href="javascript:;" wire:click="create" class="btn btn-primary radius-30 mt-2 mt-lg-0"><i
                            class="bx bxs-plus-square"></i>Add</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="card mt-4">
        @if (!empty($roles) && $roles->count())
            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th scope="col">S.No.</th>
                                <th scope="col">Name</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($roles as $index => $role)
                                <tr>
                                    <th>{{ $roles->total() - ($roles->firstItem() + $index) + 1 }}</th>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @can('roles-edit')
                                            <a wire:click="edit({{ $role->id }})"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="mx-auto bi bi-pencil-square"></i>
                                            </a>
                                        @endcan
                                        @can('roles-delete')
                                            <a wire:click="confirmDelete({{ $role->id }})"
                                                class="btn btn-sm btn-outline-danger">
                                                <i class="mx-auto bi bi-trash3"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="pagination-wrapper">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="my-2">
                <div class="alert alert-info">No Roles Found!</div>
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal show " tabindex="-1" style="opacity:1; background-color:#0606068c; display: block ">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        <div class="modal-header">
                            <h4 class="modal-title">{{ $isEdit ? 'Edit Role' : 'Create Role' }}</h4>
                            <button type="button" class="btn-close" wire:click="$set('showModal', false)"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            @csrf
                            <div class="row g-3">
                                <!-- Name -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Role Name<sup class="text-danger">*</sup></label>
                                        <input type="text" wire:model="roleName"
                                            class="form-control @error('roleName') is-invalid @enderror"
                                            placeholder="Enter Role Name">
                                    </div>
                                    @error('roleName')
                                        <span class="text-danger error">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="col-md-12">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="frmInputGroup">
                                            <label class="form-label">Permission</label>
                                        </div>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Menu</th>
                                                    <th scope="col">List</th>
                                                    <th scope="col">Add</th>
                                                    <th scope="col">Edit</th>
                                                    <th scope="col">Delete</th>
                                                    <th>
                                                        <label for="toggleAllPermissions" class="form-label m-0">
                                                            <input type="checkbox" id="toggleAllPermissions"
                                                                class="form-check-input" name="toggleAllPermissions"
                                                                wire:click="toggleAllPermissions($event.target.checked)">
                                                            Check All
                                                        </label>


                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($menus as $mkey => $mvalue)
                                                    @php
                                                        $menuPermissions = collect($mvalue)->pluck('name')->toArray();
                                                        $menuKey = 'menu_' . Str::slug($mkey);
                                                    @endphp

                                                    <tr x-data="{
                                                        permissions: @entangle('permissions').live,
                                                        menuPermissions: {{ Js::from($menuPermissions) }},
                                                        get allChecked() {
                                                            return this.menuPermissions.every(p => this.permissions.includes(p));
                                                        },
                                                        get someChecked() {
                                                            return this.menuPermissions.some(p => this.permissions.includes(p)) && !this.allChecked;
                                                        },
                                                        toggleAllPermissions(e) {
                                                            $wire.call('toggleMenuPermissions', '{{ $mkey }}', e.target.checked);
                                                        }
                                                    }">
                                                        <th>{{ ucfirst($mkey) }}</th>

                                                        @foreach ($mvalue as $pvalue)
                                                            <td>
                                                                <input type="checkbox" wire:model="permissions"
                                                                    value="{{ $pvalue['name'] }}"
                                                                    class="form-check-input">
                                                            </td>
                                                        @endforeach

                                                        <td>
                                                            <input type="checkbox" x-ref="toggleAll"
                                                                :checked="allChecked" :indeterminate.prop="someChecked"
                                                                @change="toggleAllPermissions" class="form-check-input">
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                {{ $isEdit ? 'Update' : 'Create' }}
                                <i class="spinner-border spinner-border-sm" wire:loading></i>
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
