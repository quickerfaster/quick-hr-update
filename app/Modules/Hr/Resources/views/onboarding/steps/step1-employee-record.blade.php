{{-- Onboarding Step 1: Employee Record (REQUIRED) --}}
<div>
    <h4 class="mb-1">{{ $isPreLinked ? 'Confirm Your Employee Record' : 'Create Your Employee Record' }}</h4>
    <p class="text-muted small mb-4">
        Set up your employee record to get started with the platform.
    </p>

    <form wire:submit.prevent="save">
        {{-- Name Fields --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" id="first_name" class="form-control @error('first_name') is-invalid @enderror"
                       wire:model="first_name" placeholder="Enter your first name">
                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" id="last_name" class="form-control @error('last_name') is-invalid @enderror"
                       wire:model="last_name" placeholder="Enter your last name">
                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" id="email" class="form-control @error('email') is-invalid @enderror"
                   wire:model="email" placeholder="Enter your email">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Phone & Hire Date --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" class="form-control @error('phone') is-invalid @enderror"
                       wire:model="phone" placeholder="Enter your phone number">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                <input type="date" id="hire_date" class="form-control @error('hire_date') is-invalid @enderror"
                       wire:model="hire_date">
                @error('hire_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                {{ $isPreLinked ? 'Confirm & Continue' : 'Save & Continue' }}
                <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
</div>
