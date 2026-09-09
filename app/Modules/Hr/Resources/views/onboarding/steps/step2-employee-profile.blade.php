{{-- Onboarding Step 2: Employee Profile (OPTIONAL) --}}
<div>
    <h4 class="mb-1">Employee Profile</h4>
    <p class="text-muted small mb-4">
        Add personal details and emergency contacts. All fields are optional — fill what you can and continue.
    </p>

    <form wire:submit.prevent="save">
        {{-- Section A: Personal Information --}}
        <h5 class="mb-3 text-secondary">Personal Information</h5>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                       wire:model="date_of_birth">
                @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" class="form-select @error('gender') is-invalid @enderror" wire:model="gender">
                    <option value="">Select...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="nationality" class="form-label">Nationality</label>
                <input type="text" id="nationality" class="form-control @error('nationality') is-invalid @enderror"
                       wire:model="nationality" placeholder="e.g. Nigerian">
                @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="marital_status" class="form-label">Marital Status</label>
                <select id="marital_status" class="form-select @error('marital_status') is-invalid @enderror"
                        wire:model="marital_status">
                    <option value="">Select...</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="divorced">Divorced</option>
                    <option value="widowed">Widowed</option>
                </select>
                @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="personal_email" class="form-label">Personal Email</label>
                <input type="email" id="personal_email" class="form-control @error('personal_email') is-invalid @enderror"
                       wire:model="personal_email" placeholder="you@personal.com">
                @error('personal_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="personal_phone" class="form-label">Personal Phone</label>
                <input type="text" id="personal_phone" class="form-control @error('personal_phone') is-invalid @enderror"
                       wire:model="personal_phone" placeholder="+234...">
                @error('personal_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Address --}}
        <h6 class="mb-2 text-secondary">Address</h6>
        <div class="mb-3">
            <label for="address_street" class="form-label">Street Address</label>
            <input type="text" id="address_street" class="form-control @error('address_street') is-invalid @enderror"
                   wire:model="address_street" placeholder="123 Main Street">
            @error('address_street') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="address_city" class="form-label">City</label>
                <input type="text" id="address_city" class="form-control @error('address_city') is-invalid @enderror"
                       wire:model="address_city" placeholder="Lagos">
                @error('address_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="address_state" class="form-label">State / Province</label>
                <input type="text" id="address_state" class="form-control @error('address_state') is-invalid @enderror"
                       wire:model="address_state" placeholder="Lagos State">
                @error('address_state') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="address_postal_code" class="form-label">Postal Code</label>
                <input type="text" id="address_postal_code" class="form-control @error('address_postal_code') is-invalid @enderror"
                       wire:model="address_postal_code" placeholder="100001">
                @error('address_postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="address_country" class="form-label">Country</label>
                <input type="text" id="address_country" class="form-control @error('address_country') is-invalid @enderror"
                       wire:model="address_country" placeholder="Nigeria">
                @error('address_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <hr class="my-4">

        {{-- Section B: Emergency Contact --}}
        <h5 class="mb-3 text-secondary">Emergency Contact</h5>

        <div class="mb-3">
            <label for="emergency_contact_name" class="form-label">Full Name</label>
            <input type="text" id="emergency_contact_name"
                   class="form-control @error('emergency_contact_name') is-invalid @enderror"
                   wire:model="emergency_contact_name" placeholder="John Doe">
            @error('emergency_contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="emergency_contact_phone" class="form-label">Phone</label>
                <input type="text" id="emergency_contact_phone"
                       class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                       wire:model="emergency_contact_phone" placeholder="+234...">
                @error('emergency_contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="emergency_contact_relationship" class="form-label">Relationship</label>
                <select id="emergency_contact_relationship"
                        class="form-select @error('emergency_contact_relationship') is-invalid @enderror"
                        wire:model="emergency_contact_relationship">
                    <option value="">Select...</option>
                    <option value="spouse">Spouse</option>
                    <option value="parent">Parent</option>
                    <option value="sibling">Sibling</option>
                    <option value="child">Child</option>
                    <option value="friend">Friend</option>
                    <option value="other">Other</option>
                </select>
                @error('emergency_contact_relationship') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
</div>
