<div>
    <!-- Mode Tabs -->
    <ul class="nav nav-pills mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $mode === 'single' ? 'active' : '' }}"
                    wire:click="switchMode('single')"
                    type="button">
                <i class="fas fa-user me-1"></i> Single User Assignment
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $mode === 'bulk' ? 'active' : '' }}"
                    wire:click="switchMode('bulk')"
                    type="button">
                <i class="fas fa-users me-1"></i> Bulk Assignment
            </button>
        </li>
    </ul>

    {{-- ======================================== --}}
    {{-- Single User Mode --}}
    {{-- ======================================== --}}
    @if($mode === 'single')
    <div class="row">
        <!-- Left Panel: User Selector -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Select User</h6>
                </div>
                <div class="card-body">
                    @if($selectedUser)
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                            <div>
                                <strong>{{ $selectedUser->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $selectedUser->email }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" wire:click="clearUser">
                                <i class="fas fa-times"></i> Change
                            </button>
                        </div>
                    @else
                        <div class="mb-3">
                            <input type="text"
                                   class="form-control"
                                   placeholder="Search users by name or email..."
                                   wire:model.live.debounce.300ms="search"
                                   autofocus>
                        </div>

                        @if(strlen($search) >= 2 && count($users) > 0)
                            <div class="list-group">
                                @foreach($users as $user)
                                    <a href="#"
                                       class="list-group-item list-group-item-action"
                                       wire:click.prevent="selectUser({{ $user->id }})">
                                        <strong>{{ $user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </a>
                                @endforeach
                            </div>
                        @elseif(strlen($search) >= 2 && count($users) === 0)
                            <p class="text-muted">No users found.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel: Company Assignment -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        @if($selectedUser)
                            Company Assignments for {{ $selectedUser->name }}
                        @else
                            Company Assignments
                        @endif
                    </h6>
                    @if($selectedUser)
                        <button class="btn btn-sm btn-primary" wire:click="save">
                            <i class="fas fa-save me-1"></i> Save Assignments
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($saved)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> Company assignments saved successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" wire:click="$set('saved', false)"></button>
                        </div>
                    @endif

                    @if(!$selectedUser)
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-arrow-left me-2"></i>
                            Search and select a user to manage their company assignments.
                        </p>
                    @elseif($companies->isEmpty())
                        <p class="text-muted text-center py-4">
                            No companies available. Create companies first in the Organization module.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Company</th>
                                        <th>Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           value="{{ $company->id }}"
                                                           wire:model.live="assignedCompanyIds"
                                                           id="company_{{ $company->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check-label" for="company_{{ $company->id }}">
                                                    {{ $company->name }}
                                                </label>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $company->code }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                {{ count($assignedCompanyIds) }} of {{ $companies->count() }} companies assigned
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================== --}}
    {{-- Bulk Assignment Mode --}}
    {{-- ======================================== --}}
    @else
    <div class="row">
        <!-- Left Panel: Multi-User Selector -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Select Users</h6>
                    @if(count($selectedUserIds) > 0)
                        <button class="btn btn-sm btn-outline-secondary" wire:click="clearBulk">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <input type="text"
                               class="form-control"
                               placeholder="Search users by name or email..."
                               wire:model.live.debounce.300ms="bulkSearch"
                               autofocus>
                    </div>

                    <!-- Search Results -->
                    @if(strlen($bulkSearch) >= 2 && count($bulkUsers) > 0)
                        <div class="list-group mb-3">
                            @foreach($bulkUsers as $user)
                                <a href="#"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                   wire:click.prevent="addUserToBulk({{ $user->id }})">
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    <i class="fas fa-plus text-success"></i>
                                </a>
                            @endforeach
                        </div>
                    @elseif(strlen($bulkSearch) >= 2 && count($bulkUsers) === 0)
                        <p class="text-muted mb-3">No users found.</p>
                    @endif

                    <!-- Selected Users -->
                    @if(count($selectedUsers) > 0)
                        <h6 class="text-muted mb-2">
                            {{ count($selectedUsers) }} user(s) selected
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($selectedUsers as $id => $user)
                                <span class="badge bg-primary d-flex align-items-center p-2">
                                    {{ $user['name'] }}
                                    <button type="button"
                                            class="btn-close btn-close-white ms-2"
                                            style="font-size: 0.5rem;"
                                            wire:click="removeUserFromBulk({{ $id }})"
                                            aria-label="Remove {{ $user['name'] }}">
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-search me-2"></i>
                            Search and add users to assign companies in bulk.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel: Company Assignment -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        @if(count($selectedUserIds) > 0)
                            Assign Companies to {{ count($selectedUserIds) }} User(s)
                        @else
                            Company Assignments
                        @endif
                    </h6>
                    @if(count($selectedUserIds) > 0)
                        <button class="btn btn-sm btn-primary"
                                wire:click="bulkSave"
                                {{ count($bulkAssignedCompanyIds) === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-save me-1"></i>
                            Apply to All {{ count($selectedUserIds) }} Users
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($bulkSaved)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i>
                            Companies assigned successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" wire:click="$set('bulkSaved', false)"></button>
                        </div>
                    @endif

                    @if(count($selectedUserIds) === 0)
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-arrow-left me-2"></i>
                            Search and add users to begin bulk company assignment.
                        </p>
                    @elseif($companies->isEmpty())
                        <p class="text-muted text-center py-4">
                            No companies available. Create companies first in the Organization module.
                        </p>
                    @else
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Non-destructive mode:</strong> Selected companies will be <em>added</em> to each user.
                            Existing company assignments will not be removed.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Company</th>
                                        <th>Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           value="{{ $company->id }}"
                                                           wire:model.live="bulkAssignedCompanyIds"
                                                           id="bulk_company_{{ $company->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <label class="form-check-label" for="bulk_company_{{ $company->id }}">
                                                    {{ $company->name }}
                                                </label>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $company->code }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                {{ count($bulkAssignedCompanyIds) }} of {{ $companies->count() }} companies selected
                                &mdash; will be assigned to {{ count($selectedUserIds) }} user(s)
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
