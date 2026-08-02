@extends('layouts.app')

@section('header', 'Facilities')

@section('content')
<div class="page-stack">
    <div class="page-heading">
        <div>
            <h2 class="page-title">BJMP Facilities</h2>
            <p class="page-subtitle">Add detention facilities and monitor detainee load per facility.</p>
        </div>
    </div>

    <div class="glass-panel p-4 sm:p-5">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add Detention Facility</h3>
            <p class="text-sm text-gray-500">Create a new facility record and set its capacity.</p>
        </div>

        <form action="{{ route('admin.facilities.store') }}" method="POST" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12">
            @csrf
            <div class="xl:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Facility Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="Example Jail">
            </div>
            <div class="xl:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                <input type="text" name="region" value="{{ old('region') }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="NCR">
            </div>
            <div class="md:col-span-2 xl:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="Full facility address">
            </div>
            <div class="xl:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity') }}" min="1" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="0">
            </div>
            <div class="flex items-end md:col-span-2 xl:col-span-2">
                <button type="submit" class="btn-primary w-full justify-center">Add Facility</button>
            </div>
        </form>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="table-scroll">
            <table class="data-table responsive-table">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Region</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Address</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Detainees / Capacity</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody x-data="{ openFacility: null }" class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                        @php
                            $capacity = (int) ($facility->capacity ?: 0);
                            $detainees = (int) ($facility->active_detainees_count ?? 0);
                            $occupancy = $capacity > 0 ? min(100, ($detainees / $capacity) * 100) : 0;
                            $barClass = $capacity > 0 && $detainees > $capacity ? 'bg-red-500' : ($occupancy >= 85 ? 'bg-amber-500' : 'bg-emerald-500');
                        @endphp
                        <tr class="data-row align-top">
                            <td data-label="Facility" class="whitespace-nowrap font-medium text-gray-900">
                                {{ $facility->name }}
                            </td>
                            <td data-label="Region" class="whitespace-nowrap text-gray-600">
                                {{ $facility->region }}
                            </td>
                            <td data-label="Address" class="text-gray-500">
                                {{ $facility->address }}
                            </td>
                            <td data-label="Occupancy">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="text-right whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ $detainees }}</span>
                                        <span class="text-gray-400">/ {{ $capacity ?: 'N/A' }}</span>
                                    </div>
                                    <div class="w-32">
                                        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-2 rounded-full {{ $barClass }}" style="width: {{ $capacity > 0 ? $occupancy : 0 }}%"></div>
                                        </div>
                                        <p class="mt-1 text-[11px] text-gray-500 text-right">
                                            {{ $capacity > 0 ? round($occupancy) . '%' : 'No capacity set' }}
                                            @if($capacity > 0 && $detainees > $capacity)
                                                <span class="text-red-600 font-semibold">Over capacity</span>
                                            @elseif($capacity > 0 && $occupancy >= 85)
                                                <span class="text-amber-600 font-semibold">Near limit</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Actions" class="whitespace-nowrap text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="openFacility = openFacility === {{ $facility->id }} ? null : {{ $facility->id }}" class="btn-secondary py-1.5 px-3 text-xs">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" onsubmit="return confirm('Delete this facility? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary py-1.5 px-3 text-xs text-red-600 hover:text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="openFacility === {{ $facility->id }}" x-cloak class="mobile-edit-row border-b border-gray-100 bg-gray-50/70">
                            <td colspan="5" class="px-6 py-5">
                                <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12">
                                    @csrf
                                    @method('PUT')
                                    <div class="xl:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility Name</label>
                                        <input type="text" name="name" value="{{ old('name', $facility->name) }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                        <input type="text" name="region" value="{{ old('region', $facility->region) }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="md:col-span-2 xl:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                        <input type="text" name="address" value="{{ old('address', $facility->address) }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                                        <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity) }}" min="1" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="flex items-end gap-2 md:col-span-2 xl:col-span-2">
                                        <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No facilities found. Please run the seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
