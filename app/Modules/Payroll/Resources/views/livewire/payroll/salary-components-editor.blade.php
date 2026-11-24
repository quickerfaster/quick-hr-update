<div class="card mb-3">
    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Salary Components</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addRow">
            <i class="fas fa-plus"></i> Add Component
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-items-center">
                <thead>
                    <tr>
                        <th style="width: 55%">Component</th>
                        <th>Amount ({{ $currencySymbol }})</th>
                        <th style="width: 60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $row)
                        <tr>
                            <td>
                                <select class="form-select form-select-sm" wire:model="rows.{{ $index }}.component_key">
                                    <option value="">— Select component —</option>
                                    @foreach ($componentTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="any" class="form-control form-control-sm"
                                    wire:model="rows.{{ $index }}.amount" placeholder="0.00">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="removeRow({{ $index }})">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="fw-bold">Total Gross</td>
                        <td class="fw-bold">{{ $currencySymbol }}{{ number_format($totalGross, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pensionable (BHT)</td>
                        <td class="text-muted">{{ $currencySymbol }}{{ number_format($pensionableBht, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-primary" wire:click="save">
                <i class="fas fa-save me-1"></i> Save Salary Components
            </button>
        </div>
    </div>
</div>
