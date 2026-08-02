@extends('layouts.app')

@section('header', 'Detainee Records')

@section('content')
<div class="page-stack">
    <div class="page-heading">
        <div><h2 class="page-title">Detainee Database</h2><p class="page-subtitle">Search, review, and maintain custody records.</p></div>
        @can('create', \App\Models\Detainee::class)
            <a href="{{ route('detainees.create') }}" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Detainee
            </a>
        @endcan
    </div>

    <!-- Filter Bar -->
    <div class="toolbar">
        <form action="{{ route('detainees.index') }}" method="GET" class="filter-form">
            <div class="relative sm:col-span-2 xl:min-w-80 xl:flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or charge..." class="form-control pl-10">
            </div>
            
            <select name="record_filter" onchange="this.form.submit()" class="form-control xl:w-56">
                <option value="" {{ request('record_filter') === null || request('record_filter') === '' ? 'selected' : '' }}>All Statuses and Alerts</option>
                <optgroup label="Detainee status">
                    <option value="status:active" {{ request('record_filter') === 'status:active' ? 'selected' : '' }}>Active</option>
                    <option value="status:released" {{ request('record_filter') === 'status:released' ? 'selected' : '' }}>Released</option>
                    <option value="status:archived" {{ request('record_filter') === 'status:archived' ? 'selected' : '' }}>Archived</option>
                </optgroup>
                <optgroup label="Alert level">
                    <option value="alert:critical" {{ request('record_filter') === 'alert:critical' ? 'selected' : '' }}>Critical</option>
                    <option value="alert:at_risk" {{ request('record_filter') === 'alert:at_risk' ? 'selected' : '' }}>At Risk</option>
                    <option value="alert:flagged" {{ request('record_filter') === 'alert:flagged' ? 'selected' : '' }}>Flagged</option>
                    <option value="alert:monitored" {{ request('record_filter') === 'alert:monitored' ? 'selected' : '' }}>Monitored</option>
                    <option value="alert:resolved" {{ request('record_filter') === 'alert:resolved' ? 'selected' : '' }}>Resolved</option>
                </optgroup>
            </select>
            
            <select name="facility_id" onchange="this.form.submit()" class="form-control xl:w-56">
                <option value="">All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn-primary py-2 px-4 text-sm whitespace-nowrap">
                Search
            </button>
            
            @if(request()->hasAny(['search', 'record_filter', 'facility_id']))
                <a href="{{ route('detainees.index') }}" class="btn-secondary py-2 px-4 text-sm whitespace-nowrap">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Detainees Table -->
    <div class="glass-panel overflow-hidden">
        <div class="table-scroll">
            <table class="data-table responsive-table">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Detainee Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Primary Charge</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Commitment Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status / Alert Level</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($detainees as $detainee)
                        <tr class="data-row">
                            <td data-label="Detainee" class="whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-taya-navy-100 text-taya-navy-700 flex items-center justify-center font-bold text-xs">
                                        {{ substr($detainee->full_name, 0, 1) }}
                                    </div>
                                    <a href="{{ route('detainees.show', $detainee) }}" class="font-bold text-gray-900 hover:text-taya-accent transition-colors">
                                        {{ $detainee->full_name }}
                                    </a>
                                </div>
                            </td>
                            <td data-label="Primary charge">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 font-medium">{{ $detainee->penaltyReference->charge_name }}</span>
                                    <span class="text-xs text-gray-500 truncate max-w-xs">{{ $detainee->charge_description }}</span>
                                </div>
                            </td>
                            <td data-label="Facility" class="whitespace-nowrap text-gray-600">
                                {{ $detainee->facility->name }}
                            </td>
                            <td data-label="Commitment date" class="whitespace-nowrap">
                                <span class="text-gray-900">{{ $detainee->commitment_date->format('M d, Y') }}</span>
                                <span class="text-xs text-gray-500 block">({{ $detainee->days_detained }} days ago)</span>
                            </td>
                            <td data-label="Status / alert" class="whitespace-nowrap">
                                <div class="flex flex-col gap-1.5 items-start">
                                    @if($detainee->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($detainee->status) }}
                                        </span>
                                    @endif
                                    
                                    @if($detainee->status === 'active' && $detainee->alerts->isNotEmpty())
                                        @php $alert = $detainee->alerts->first(); @endphp
                                        <span class="badge badge-{{ $alert->alert_level }} text-[10px] uppercase tracking-wider px-1.5 py-0">
                                            {{ str_replace('_', ' ', $alert->alert_level) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Action" class="whitespace-nowrap text-right">
                                <a href="{{ route('detainees.show', $detainee) }}" class="text-taya-accent hover:text-taya-accent-dark font-medium text-sm">
                                    View Details &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No detainee records found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($detainees->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $detainees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
