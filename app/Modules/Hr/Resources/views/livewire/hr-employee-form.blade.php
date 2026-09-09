<div>

    <div class="form-wrapper">

        @php
            $module = strtolower($this->getConfigResolver()->getModuleName());
            $modelName = $this->getConfigResolver()->getModelName();
            $displayModelName = ucwords(str_replace(['_', '-'], ' ', \Str::snake($modelName)));
            $modelPlural = \Str::plural(\Str::kebab($modelName));

            $params = $this->returnParams ?? [];
            $queryString = !empty($params) ? '?' . http_build_query($params) : '';
            $backUrl = url("/{$module}/{$modelPlural}" . $queryString);

            $crudType = $this->crudType;
        @endphp

        {{-- 1. HEADER SECTION --}}
        <div class="container-xl mb-4" style="max-width: 900px; margin: 0 auto;">
            <div class="py-4">
                @if ($crudType == 'pages')
                    <a href="{{ $backUrl }}"
                        class="text-decoration-none text-muted small fw-bold mb-2 d-inline-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i> Back to {{ \Str::plural($displayModelName) }}
                    </a>
                @endif

                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold text-dark mb-0">
                            {{ $isEditMode ? 'Edit' : 'Create New' }} {{ $displayModelName }}
                        </h2>
                        @if ($isEditMode)
                            <span class="text-muted small">Modifying record ID: #{{ $recordId }}</span>
                        @else
                            <p class="text-muted small mb-0">Fill in the details below to add a new record to the
                                system.</p>
                        @endif

                        @include('qf::components.layouts.partials.company-title-suffix', ['asBadge' => true])
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save">
            <div class="container-xl" style="max-width: 900px; margin: 0 auto;">

                {{-- Error Handling --}}
                @if ($errors->any())
                    <div class="alert alert-light border-start border-danger border-4 shadow-sm mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                            <h6 class="text-danger fw-bold mb-0">Please fix the following:</h6>
                        </div>
                        <ul class="mb-0 small text-danger">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Loop through groups as Vertical Sections --}}
                <div class="form-sections-container pb-5">
                    @foreach ($displayGroups as $groupKey => $group)
                        <div class="form-section mb-5">
                            <div class="section-header mb-4 border-bottom pb-2 d-flex align-items-center">
                                <div class="bg-primary-subtle rounded-circle p-2 me-3 d-inline-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <span class="text-primary fw-bold small">{{ $loop->iteration }}</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-0">{{ $group['title'] ?? ucfirst($groupKey) }}</h5>
                            </div>

                            <div class="section-body">
                                <div class="row g-3 justify-content-center">
                                    @foreach ($group['fields'] as $field)
                                        @if (!$this->isFieldHidden($field, $isEditMode ? 'onEditForm' : 'onNewForm'))
                                            <div class="col-12 @if($crudType != 'drawers') col-lg-8 @endif">
                                                {!! $this->getField($field)->renderForm($this->fields[$field] ?? null) !!}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ============================================ --}}
                {{-- HR EXTENSION: Send Invitation Checkbox        --}}
                {{-- ============================================ --}}
                @if (! $isEditMode)
                    <div class="form-section mb-5">
                        <div class="section-header mb-4 border-bottom pb-2 d-flex align-items-center">
                            <div class="bg-info-subtle rounded-circle p-2 me-3 d-inline-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px;">
                                <span class="text-info fw-bold small">
                                    <i class="fas fa-paper-plane"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-0">Account Setup</h5>
                        </div>

                        <div class="section-body">
                            <div class="row g-3 justify-content-center">
                                <div class="col-12 col-lg-8">
                                    <div class="card border-info-subtle bg-info-subtle bg-opacity-10">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                    type="checkbox"
                                                    id="sendInvitation"
                                                    wire:model="sendInvitation"
                                                    style="width: 1.2em; height: 1.2em; margin-top: 0.15em;">
                                                <label class="form-check-label ms-2" for="sendInvitation">
                                                    <strong>Send account invitation after saving</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        An email will be sent to the employee's email address
                                                        inviting them to create their account. The invitation
                                                        will be pre-linked to this employee record.
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="sticky-action-bar border-top bg-white bg-opacity-75 backdrop-blur py-3 px-4 shadow-lg"
                style="position: sticky; bottom: 0; z-index: 1020; margin-left: -1.5rem; margin-right: -1.5rem; margin-bottom: -1.5rem;">
                <div class="container-fluid d-flex justify-content-between align-items-center">

                    @if ($inline)
                        <button type="button" wire:click="$dispatch('closeDrawer')"
                            class="btn btn-link text-muted text-decoration-none fw-bold p-0">
                            <i class="fas fa-arrow-left me-1"></i> Discard Changes
                        </button>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold">
                                Save Changes
                            </button>
                        </div>
                    @else
                        <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold p-0"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                            Save Record
                        </button>
                    @endif

                </div>
            </div>

            <style>
                .backdrop-blur {
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                }

                @media (max-width: 768px) {
                    .sticky-action-bar {
                        margin-left: -1rem;
                        margin-right: -1rem;
                        padding: 1rem;
                    }
                }
            </style>

        </form>
    </div>

    <style>
        .form-section:last-child {
            margin-bottom: 2rem !important;
        }

        .section-header h5 {
            letter-spacing: -0.01em;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }
    </style>

</div>
