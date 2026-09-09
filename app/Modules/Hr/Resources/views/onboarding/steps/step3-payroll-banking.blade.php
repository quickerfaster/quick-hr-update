{{-- Onboarding Step 3: Payroll & Banking (OPTIONAL) --}}
<div>
    @if (! $payrollAvailable)
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            The Payroll module is not currently installed. This step has been skipped automatically.
        </div>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" wire:click="save">
                Continue
                <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    @else
        <h4 class="mb-1">Payroll & Banking</h4>
        <p class="text-muted small mb-4">
            Set up your bank details for salary payments. All fields are optional.
        </p>

        <form wire:submit.prevent="save">
            <div class="mb-3">
                <label for="bank_name" class="form-label">Bank Name</label>
                <input type="text" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                       wire:model="bank_name" placeholder="e.g. Access Bank">
                @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="account_name" class="form-label">Account Name</label>
                <input type="text" id="account_name" class="form-control @error('account_name') is-invalid @enderror"
                       wire:model="account_name" placeholder="Name on bank account">
                @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="account_number" class="form-label">Account Number</label>
                    <input type="text" id="account_number" class="form-control @error('account_number') is-invalid @enderror"
                           wire:model="account_number" placeholder="10-digit NUBAN">
                    @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="bank_code" class="form-label">Bank Code (Sort Code)</label>
                    <input type="text" id="bank_code" class="form-control @error('bank_code') is-invalid @enderror"
                           wire:model="bank_code" placeholder="e.g. 044">
                    @error('bank_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" wire:click="skip">
                    Skip for Now
                </button>
                <button type="submit" class="btn btn-primary">
                    Save & Continue
                    <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    @endif
</div>
