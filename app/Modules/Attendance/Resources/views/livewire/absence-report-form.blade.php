<div>
    <div class="row">
        {{-- Absence Type --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Absence Type</label>
            <select class="form-select @error('absenceType') is-invalid @enderror" wire:model="absenceType">
                <option value="sick_leave">Sick Leave</option>
                <option value="emergency">Family Emergency</option>
                <option value="personal">Personal Matter</option>
                <option value="official_assignment">Official Assignment / Company Duty</option>
                <option value="other">Other</option>
            </select>
            @error('absenceType') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Start Date --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control @error('startDate') is-invalid @enderror"
                wire:model="startDate" max="{{ date('Y-m-d') }}">
            @error('startDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Expected Duration --}}
        <div class="col-md-6 mb-3">
            <label class="form-label">Expected Duration</label>
            <select class="form-select" wire:model="expectedDuration">
                <option value="1">1 day</option>
                <option value="3">2–3 days</option>
                <option value="5">4–5 days (about a week)</option>
                <option value="14">1–2 weeks</option>
                <option value="28">3–4 weeks (about a month)</option>
                <option value="60">1–2 months</option>
                <option value="0">Uncertain</option>
            </select>
            <small class="text-muted">
                <strong>Official Assignment</strong>: management-directed duty (training, trip, secondment) —
                paid, not deducted from leave balance.
                <br>
                <strong>Sick / Emergency / Personal</strong>: unplanned absence — may create a draft
                leave request for formal recording.
            </small>
        </div>

        {{-- Reason --}}
        <div class="col-12 mb-3">
            <label class="form-label">Reason <span class="text-muted">(min 10 characters)</span></label>
            <textarea class="form-control @error('absenceReason') is-invalid @enderror" rows="3"
                wire:model="absenceReason" placeholder="Brief explanation for your absence..."></textarea>
            @error('absenceReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Notification & Tracking --}}
        <div class="col-12 mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="notifyManager" id="notifyManager">
                <label class="form-check-label" for="notifyManager">
                    <strong>Notify manager immediately</strong>
                </label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" wire:model="createLeaveRequest" id="createLeaveRequest">
                <label class="form-check-label" for="createLeaveRequest">
                    <strong>Create draft leave request for formal tracking</strong>
                    <br>
                    <small class="text-muted">
                        Required for Sick Leave and Personal Matter — ensures proper leave balance tracking.
                        Not applicable for Official Assignment.
                    </small>
                </label>
            </div>
        </div>
    </div>
</div>
