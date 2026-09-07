<div>
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
</div>
