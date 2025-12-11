<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Renstra: {{ $renstra->kode_renstra }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.renstra.pdf', $renstra) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    Export PDF
                </a>
                @can('manage-renstra')
                <a href="{{ route('renstra.edit', $renstra) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    Edit
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Kode Renstra</h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $renstra->kode_renstra }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Status</h4>
                            <span class="mt-1 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($renstra->status == 'active') bg-green-100 text-green-800
                                @elseif($renstra->status == 'draft') bg-yellow-100 text-yellow-800
                                @elseif($renstra->status == 'completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($renstra->status) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Periode</h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $renstra->tahun_awal }} - {{ $renstra->tahun_akhir }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Kategori</h4>
                            <p class="mt-1 text-gray-900">{{ $renstra->kategori?->nama_kategori ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Kegiatan</h4>
                            <p class="mt-1 text-gray-900">{{ $renstra->kegiatan?->nama_kegiatan ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Program Studi</h4>
                            <p class="mt-1 text-gray-900">{{ $renstra->prodi?->nama_prodi ?? 'Semua Prodi' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500">Indikator</h4>
                        <p class="mt-1 text-gray-900">{{ $renstra->indikator }}</p>
                    </div>

                    @if($renstra->keterangan)
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500">Keterangan</h4>
                        <p class="mt-1 text-gray-900">{{ $renstra->keterangan }}</p>
                    </div>
                    @endif

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                            <div>
                                <span>Dibuat oleh: {{ $renstra->user?->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span>Tanggal: {{ $renstra->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target per Year -->
            @if($renstra->indikatorRelation && $renstra->indikatorRelation->targets->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Target Tahunan</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($renstra->indikatorRelation->targets->sortBy('tahun') as $target)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $target->tahun }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($target->target_value, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $target->satuan ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $target->keterangan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Evaluasi History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Evaluasi</h3>
                        @can('kaprodi')
                        <a href="{{ route('evaluasi.create', ['renstra_id' => $renstra->id]) }}" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-green-700">
                            + Input Evaluasi
                        </a>
                        @endcan
                    </div>
                    
                    @if($renstra->evaluasis->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Realisasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ketercapaian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($renstra->evaluasis as $evaluasi)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $evaluasi->prodi?->nama_prodi ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($evaluasi->realisasi, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $evaluasi->ketercapaian >= 100 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ number_format($evaluasi->ketercapaian, 2) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($evaluasi->status == 'approved') bg-green-100 text-green-800
                                            @elseif($evaluasi->status == 'verified') bg-blue-100 text-blue-800
                                            @elseif($evaluasi->status == 'submitted') bg-yellow-100 text-yellow-800
                                            @elseif($evaluasi->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($evaluasi->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('evaluasi.show', $evaluasi) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 text-sm">Belum ada data evaluasi untuk renstra ini.</p>
                    @endif
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('renstra.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Kembali ke Daftar Renstra</a>
            </div>
        </div>
    </div>
</x-app-layout>
