@extends('layouts.app')

@section('header', 'Penalty References')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Penalty Reference Matrix</h2>
            <p class="text-sm text-gray-500 mt-1">Manage RPC/RA charges and their maximum imposable penalties.</p>
        </div>
    </div>

    <div class="glass-panel p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add Penalty Reference</h3>
            <p class="text-sm text-gray-500">Create a new RPC/RA charge entry for detainee computations and case reporting.</p>
        </div>

        <form action="{{ route('admin.penalties.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            @csrf
            <div class="xl:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">RPC/RA Code</label>
                <input type="text" name="rpc_code" value="{{ old('rpc_code') }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="Art. 308">
            </div>
            <div class="xl:col-span-2 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Charge Description</label>
                <input type="text" name="charge_name" value="{{ old('charge_name') }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="Theft">
            </div>
            <div class="xl:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Penalty (Years)</label>
                <input type="number" name="max_penalty_years" value="{{ old('max_penalty_years') }}" min="0" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="0">
            </div>
            <div class="xl:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Penalty (Months)</label>
                <input type="number" name="max_penalty_months" value="{{ old('max_penalty_months') }}" min="0" class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent" placeholder="0">
            </div>
            <div class="xl:col-span-1 md:col-span-2 xl:col-start-5 flex items-end">
                <button type="submit" class="btn-primary w-full justify-center">Add Reference</button>
            </div>
        </form>
    </div>

    <div class="glass-panel overflow-hidden" x-data="{ openPenalty: null }">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">RPC/RA Code</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Charge Description</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Max Penalty (Years)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Max Penalty (Months)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penalties as $penalty)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $penalty->rpc_code }}
                                <span class="badge bg-gray-100 text-gray-600 ml-2">{{ $penalty->law_source }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $penalty->charge_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900">
                                {{ $penalty->max_penalty_years }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ $penalty->max_penalty_months ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="openPenalty = openPenalty === {{ $penalty->id }} ? null : {{ $penalty->id }}" class="btn-secondary py-1.5 px-3 text-xs">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.penalties.destroy', $penalty) }}" method="POST" onsubmit="return confirm('Warning: Deleting this reference might affect existing detainee computations. Proceed?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-secondary py-1.5 px-3 text-xs text-red-600 hover:text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="openPenalty === {{ $penalty->id }}" x-cloak class="bg-gray-50/70">
                            <td colspan="5" class="px-6 py-5">
                                <form action="{{ route('admin.penalties.update', $penalty) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                                    @csrf
                                    @method('PUT')
                                    <div class="xl:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">RPC/RA Code</label>
                                        <input type="text" name="rpc_code" value="{{ old('rpc_code', $penalty->rpc_code) }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-2 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Charge Description</label>
                                        <input type="text" name="charge_name" value="{{ old('charge_name', $penalty->charge_name) }}" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Penalty (Years)</label>
                                        <input type="number" name="max_penalty_years" value="{{ old('max_penalty_years', $penalty->max_penalty_years) }}" min="0" required class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Penalty (Months)</label>
                                        <input type="number" name="max_penalty_months" value="{{ old('max_penalty_months', $penalty->max_penalty_months) }}" min="0" class="w-full rounded-lg border-gray-300 focus:ring-taya-accent focus:border-taya-accent">
                                    </div>
                                    <div class="xl:col-span-1 md:col-span-2 xl:col-start-5 flex items-end">
                                        <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No penalty references found. Please run the seeder.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($penalties->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $penalties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
