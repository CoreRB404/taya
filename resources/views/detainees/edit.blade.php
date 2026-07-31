@extends('layouts.app')

@section('header', 'Edit Detainee')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('detainees.show', $detainee) }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Profile
        </a>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-900">Edit Detainee: {{ $detainee->full_name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Update commitment details or primary charge. Note: Changing the charge will recalculate overstay alerts during the next nightly run.</p>
        </div>

        <form action="{{ route('detainees.update', $detainee) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Personal Info -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <div class="mt-1">
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $detainee->full_name) }}" required
                               class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                    </div>
                    @error('full_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Facility -->
                    <div>
                        <label for="facility_id" class="block text-sm font-medium text-gray-700">Detention Facility</label>
                        <div class="mt-1">
                            <select name="facility_id" id="facility_id" required
                                    class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ old('facility_id', $detainee->facility_id) == $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Commitment Date -->
                    <div>
                        <label for="commitment_date" class="block text-sm font-medium text-gray-700">Commitment Date</label>
                        <div class="mt-1">
                            <input type="date" name="commitment_date" id="commitment_date" value="{{ old('commitment_date', $detainee->commitment_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                                   class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Must not be a future date.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="bail_amount" class="block text-sm font-medium text-gray-700">Bail Amount</label>
                        <div class="mt-1">
                            <input type="number" name="bail_amount" id="bail_amount" value="{{ old('bail_amount', $detainee->bail_amount) }}" min="0"
                                   class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg"
                                   placeholder="0">
                        </div>
                        @error('bail_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="bail_status" class="block text-sm font-medium text-gray-700">Bail Status</label>
                        <div class="mt-1">
                            <select name="bail_status" id="bail_status"
                                    class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                                <option value="not_posted" {{ old('bail_status', $detainee->bail_status) === 'not_posted' ? 'selected' : '' }}>Not Posted</option>
                                <option value="posted" {{ old('bail_status', $detainee->bail_status) === 'posted' ? 'selected' : '' }}>Posted</option>
                                <option value="unable_to_pay" {{ old('bail_status', $detainee->bail_status) === 'unable_to_pay' ? 'selected' : '' }}>Unable to Pay</option>
                                <option value="pending_review" {{ old('bail_status', $detainee->bail_status) === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                            </select>
                        </div>
                        @error('bail_status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="bail_posted_at" class="block text-sm font-medium text-gray-700">Bail Posted Date</label>
                        <div class="mt-1">
                            <input type="date" name="bail_posted_at" id="bail_posted_at" value="{{ old('bail_posted_at', optional($detainee->bail_posted_at)->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                                   class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                        </div>
                        @error('bail_posted_at')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="bail_notes" class="block text-sm font-medium text-gray-700">Bail Notes</label>
                    <div class="mt-1">
                        <textarea id="bail_notes" name="bail_notes" rows="3"
                                  class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border border-gray-300 rounded-lg"
                                  placeholder="Describe affordability, ability to post bail, or special bail conditions.">{{ old('bail_notes', $detainee->bail_notes) }}</textarea>
                    </div>
                    @error('bail_notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="relative_name" class="block text-sm font-medium text-gray-700">Relative Name</label>
                        <div class="mt-1">
                            <input type="text" name="relative_name" id="relative_name" value="{{ old('relative_name', $detainee->relative_name) }}"
                                   class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                        </div>
                        @error('relative_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="relative_phone" class="block text-sm font-medium text-gray-700">Relative Phone Number</label>
                        <div class="mt-1">
                            <input type="text" name="relative_phone" id="relative_phone" value="{{ old('relative_phone', $detainee->relative_phone) }}"
                                   class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                        </div>
                        @error('relative_phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="relative_email" class="block text-sm font-medium text-gray-700">Relative Email</label>
                    <div class="mt-1">
                        <input type="email" name="relative_email" id="relative_email" value="{{ old('relative_email', $detainee->relative_email) }}"
                               class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                    </div>
                    @error('relative_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start gap-3">
                    <div class="mt-2">
                        <input type="checkbox" name="tracking_enabled" id="tracking_enabled" value="1" {{ old('tracking_enabled', $detainee->tracking_enabled) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                    </div>
                    <div>
                        <label for="tracking_enabled" class="font-medium text-gray-900">Enable public tracking</label>
                        <p class="text-sm text-gray-500">Allow this detainee to be looked up by tracking code.</p>
                    </div>
                </div>

                <!-- Charge Reference (Penalty mapping) -->
                <div>
                    <label for="charge_rpc_code" class="block text-sm font-medium text-gray-700">Primary Charge Category (RPC/RA)</label>
                    <div class="mt-1">
                        <select name="charge_rpc_code" id="charge_rpc_code" required
                                class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border-gray-300 rounded-lg">
                            @foreach($penalties as $penalty)
                                <option value="{{ $penalty->id }}" {{ old('charge_rpc_code', $detainee->charge_rpc_code) == $penalty->id ? 'selected' : '' }}>
                                    [{{ $penalty->rpc_code }}] {{ $penalty->charge_name }} (Max: {{ $penalty->max_penalty_years }} yrs)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Specific Charge Description -->
                <div>
                    <label for="charge_description" class="block text-sm font-medium text-gray-700">Specific Charge Description</label>
                    <div class="mt-1">
                        <textarea id="charge_description" name="charge_description" rows="3" required
                                  class="shadow-sm focus:ring-taya-accent focus:border-taya-accent block w-full sm:text-sm border border-gray-300 rounded-lg">{{ old('charge_description', $detainee->charge_description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-6 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('detainees.show', $detainee) }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Update Detainee Record
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
