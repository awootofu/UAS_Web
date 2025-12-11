<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Daftar RTL') }}</h2>
            @can('gkm')
            <a href="{{ route('rtl.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">+ Buat RTL</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        @if(Auth::user()->isAdmin() || Auth::user()->isDekan() || Auth::user()->isGPM())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prodi</label>
                            <select name="prodi" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Semua Prodi</option>
                                @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('prodi') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="ml-2 text-sm text-gray-700">Hanya Overdue</span>
                            </label>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filter</button>
                            <a href="{{ route('rtl.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RTL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rtls as $rtl)
                            <tr class="{{ $rtl->isOverdue() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ Str::limit($rtl->rtl, 50) }}</div>
                                    <div class="text-gray-500 text-xs">Evaluasi: {{ $rtl->evaluasi?->renstra?->kode_renstra }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rtl->prodi?->nama_prodi }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $rtl->pic_rtl }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $rtl->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $rtl->deadline->format('d M Y') }}
                                    @if($rtl->days_until_deadline < 0 && !in_array($rtl->status, ['completed', 'cancelled']))
                                    <br><span class="text-xs">({{ abs($rtl->days_until_deadline) }} hari terlambat)</span>
                                    @elseif($rtl->days_until_deadline <= 7 && $rtl->days_until_deadline >= 0)
                                    <br><span class="text-xs text-yellow-600">({{ $rtl->days_until_deadline }} hari lagi)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($rtl->status == 'completed') bg-green-100 text-green-800
                                        @elseif($rtl->status == 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($rtl->isOverdue()) bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $rtl->isOverdue() && $rtl->status != 'completed' ? 'Overdue' : ucfirst(str_replace('_', ' ', $rtl->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('rtl.show', $rtl) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                    @if($rtl->canEdit() && (Auth::user()->isAdmin() || (Auth::user()->isGKM() && Auth::user()->prodi_id == $rtl->prodi_id)))
                                    <a href="{{ route('rtl.edit', $rtl) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data RTL.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $rtls->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
