<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Evaluasi
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif

            <!-- Main Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $evaluasi->renstra?->kode_renstra }}</h3>
                            <p class="text-gray-500">{{ $evaluasi->renstra?->indikator }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($evaluasi->status == 'approved') bg-green-100 text-green-800
                            @elseif($evaluasi->status == 'verified') bg-blue-100 text-blue-800
                            @elseif($evaluasi->status == 'submitted') bg-yellow-100 text-yellow-800
                            @elseif($evaluasi->status == 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($evaluasi->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Prodi</h4>
                            <p class="mt-1 text-gray-900">{{ $evaluasi->prodi?->nama_prodi }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Periode</h4>
                            <p class="mt-1 text-gray-900">{{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Realisasi</h4>
                            <p class="mt-1 text-gray-900">{{ number_format($evaluasi->realisasi, 2) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Ketercapaian</h4>
                            <span class="mt-1 px-2 py-1 text-sm rounded-full {{ $evaluasi->ketercapaian >= 100 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ number_format($evaluasi->ketercapaian, 2) }}%
                            </span>
                        </div>
                    </div>

                    @if($evaluasi->faktor_pendukung)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Faktor Pendukung</h4>
                        <p class="mt-1 text-gray-900">{{ $evaluasi->faktor_pendukung }}</p>
                    </div>
                    @endif

                    @if($evaluasi->akar_masalah)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Akar Masalah</h4>
                        <p class="mt-1 text-gray-900">{{ $evaluasi->akar_masalah }}</p>
                    </div>
                    @endif

                    @if($evaluasi->faktor_penghambat)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Faktor Penghambat</h4>
                        <p class="mt-1 text-gray-900">{{ $evaluasi->faktor_penghambat }}</p>
                    </div>
                    @endif

                    @if($evaluasi->bukti)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Bukti/Evidence</h4>
                        <a href="{{ Storage::url($evaluasi->bukti->file_path) }}" target="_blank" class="mt-1 inline-flex items-center text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ $evaluasi->bukti->nama_file }} ({{ $evaluasi->bukti->formatted_size }})
                        </a>
                    </div>
                    @endif

                    <div class="pt-4 border-t border-gray-200 text-sm text-gray-500">
                        <p>Dibuat oleh: {{ $evaluasi->creator?->name }} pada {{ $evaluasi->created_at->format('d M Y H:i') }}</p>
                        @if($evaluasi->verifier)
                        <p>Diverifikasi oleh: {{ $evaluasi->verifier->name }} pada {{ $evaluasi->verified_at?->format('d M Y H:i') }}</p>
                        @endif
                        @if($evaluasi->approver)
                        <p>Diapprove oleh: {{ $evaluasi->approver->name }} pada {{ $evaluasi->approved_at?->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                    <div class="flex flex-wrap gap-3">
                        @if($evaluasi->canEdit() && (Auth::user()->isAdmin() || (Auth::user()->isKaprodi() && Auth::user()->prodi_id == $evaluasi->prodi_id)))
                        <a href="{{ route('evaluasi.edit', $evaluasi) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Edit</a>
                        <form action="{{ route('evaluasi.submit', $evaluasi) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Submit untuk Verifikasi</button>
                        </form>
                        @endif

                        @can('verify', $evaluasi)
                        @if($evaluasi->canVerify())
                        <form action="{{ route('evaluasi.verify', $evaluasi) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Verifikasi</button>
                        </form>
                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Tolak</button>
                        @endif
                        @endcan

                        @can('approve', $evaluasi)
                        @if($evaluasi->canApprove())
                        <form action="{{ route('evaluasi.approve', $evaluasi) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Approve</button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>

            <!-- RTL Section -->
            @if(!$evaluasi->isAchieved())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rencana Tindak Lanjut (RTL)</h3>
                        @can('gkm')
                        <a href="{{ route('rtl.create', ['evaluasi_id' => $evaluasi->id]) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">+ Buat RTL</a>
                        @endcan
                    </div>
                    @if($evaluasi->rtls->count() > 0)
                    <div class="space-y-3">
                        @foreach($evaluasi->rtls as $rtl)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium">{{ Str::limit($rtl->rtl, 100) }}</p>
                                    <p class="text-sm text-gray-500">PIC: {{ $rtl->pic_rtl }} | Deadline: {{ $rtl->deadline->format('d M Y') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($rtl->status == 'completed') bg-green-100 text-green-800
                                    @elseif($rtl->isOverdue()) bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $rtl->isOverdue() ? 'Overdue' : ucfirst(str_replace('_', ' ', $rtl->status)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-sm">Belum ada RTL untuk evaluasi ini.</p>
                    @endif
                </div>
            </div>
            @endif

            <a href="{{ route('evaluasi.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Kembali ke Daftar Evaluasi</a>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Tolak Evaluasi</h3>
            <form action="{{ route('evaluasi.reject', $evaluasi) }}" method="POST">
                @csrf
                <textarea name="rejection_notes" rows="4" required placeholder="Alasan penolakan..." class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
