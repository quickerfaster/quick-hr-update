<div>
    <div class="detail-page-wrapper mb-5 p-2">
        <div class="d-flex justify-content-between align-items-center my-4 d-print-none">
            <div>
                <a href="{{ url()->previous('/payroll/payroll-payslips') }}"
                    class="text-decoration-none text-muted small fw-bold mb-2 d-inline-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i> Go Back
                </a>
                <h2 class="fw-bold text-dark mb-0">Payroll Payslip Details
                    <span class="badge bg-light text-secondary border ms-3">ID: #{{ $payslip->id }}</span>
                </h2>
            </div>
            <a href="{{ route('generic.print', ['configKey' => $configKey, 'id' => $payslip->id]) }}"
                target="_blank" class="btn btn-outline-secondary shadow-sm px-3">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>

        <div class="row g-4">
            @forelse($fieldGroups as $group)
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        @if (!empty($group['title']))
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 class="fw-bold text-primary mb-0">{{ $group['title'] }}</h5>
                            </div>
                        @endif
                        <div class="card-body p-4">
                            <div class="row gy-3">
                                @foreach ($group['fields'] as $field)
                                    @if (!in_array($field, $hiddenFields['onDetail'] ?? []))
                                        <div class="col-sm-4 text-muted fw-semibold small text-uppercase">
                                            {{ $this->getFieldLabel($field) }}
                                        </div>
                                        <div class="col-sm-8 text-dark fw-medium border-bottom pb-2 border-light">
                                            {!! $this->renderFieldValue($field, $payslip->$field) !!}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">No field groups configured.</div></div>
            @endforelse
        </div>
    </div>

    <style>
        .detail-page-wrapper { font-size: 0.95rem; }
        .card { border-radius: 12px; }
        @media print {
            .d-print-none, .btn, nav, .sidebar { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .col-sm-4 { width: 30% !important; float: left; }
            .col-sm-8 { width: 70% !important; float: left; }
        }
    </style>
</div>
