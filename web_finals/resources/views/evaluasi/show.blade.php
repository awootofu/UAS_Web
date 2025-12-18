<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Evaluasi') }}
            </h2>
            <a href="{{ route('evaluasi.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-xs font-semibold uppercase hover:bg-gray-600 transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ $evaluasi->renstra->kode ?? 'RENSTRA' }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1 max-w-2xl">
                                {{ $evaluasi->renstra->deskripsi ?? 'Deskripsi Renstra' }}
                            </p>
                            <div class="mt-4 flex gap-6 text-sm">
                                <div>
                                    <span class="block text-gray-500 text-xs uppercase">Prodi</span>
                                    <span class="font-medium">{{ $evaluasi->prodi->nama_prodi ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-gray-500 text-xs uppercase">Periode</span>
                                    <span class="font-medium">{{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }}</span>
                                </div>
                                <div>
                                    <span class="block text-gray-500 text-xs uppercase">Target</span>
                                    <span class="font-medium">{{ $evaluasi->target->nilai_target ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                @if($evaluasi->status == 'approved') bg-green-100 text-green-800
                                @elseif($evaluasi->status == 'verified') bg-blue-100 text-blue-800
                                @elseif($evaluasi->status == 'submitted') bg-yellow-100 text-yellow-800
                                @elseif($evaluasi->status == 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($evaluasi->status) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                Dibuat: {{ $evaluasi->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-800 mb-4 border-b pb-2">Capaian</h4>
                        
                        <div class="mb-4">
                            <span class="block text-gray-500 text-xs uppercase">Realisasi</span>
                            <span class="text-2xl font-bold text-gray-900">{{ number_format($evaluasi->realisasi, 2) }}</span>
                        </div>

                        <div>
                            <span class="block text-gray-500 text-xs uppercase">Persentase</span>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold {{ $evaluasi->ketercapaian >= 100 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($evaluasi->ketercapaian, 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h4 class="font-semibold text-gray-800 mb-4 border-b pb-2">Bukti Dukung</h4>
                        @if($evaluasi->bukti)
                            <a href="{{ Storage::url($evaluasi->bukti->file_path) }}" target="_blank" class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 transition group">
                                <div class="bg-blue-100 p-2 rounded text-blue-600 group-hover:bg-blue-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $evaluasi->bukti->nama_file }}</p>
                                    <p class="text-xs text-gray-500">Klik untuk unduh</p>
                                </div>
                            </a>
                        @else
                            <p class="text-sm text-gray-500 italic">Tidak ada bukti diupload.</p>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 bg-white shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-semibold text-gray-800 mb-4 border-b pb-2">Analisis Evaluasi</h4>
                    
                    <div class="space-y-6">
                        @if($evaluasi->faktor_pendukung)
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Faktor Pendukung</label>
                            <div class="bg-green-50 p-3 rounded-md text-sm text-gray-800 border-l-4 border-green-400">
                                {{ $evaluasi->faktor_pendukung }}
                            </div>
                        </div>
                        @endif

                        @if($evaluasi->akar_masalah)
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Akar Masalah</label>
                            <div class="bg-red-50 p-3 rounded-md text-sm text-gray-800 border-l-4 border-red-400">
                                {{ $evaluasi->akar_masalah }}
                            </div>
                        </div>
                        @endif

                        @if($evaluasi->faktor_penghambat)
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Faktor Penghambat & Solusi</label>
                            <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-800 border-l-4 border-gray-400">
                                {{ $evaluasi->faktor_penghambat }}
                            </div>
                        </div>
                        @endif

                        @if(!$evaluasi->faktor_pendukung && !$evaluasi->akar_masalah && !$evaluasi->faktor_penghambat)
                            <p class="text-gray-400 italic text-sm">Belum ada analisis yang diisi.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h4 class="font-semibold text-gray-800 mb-4 border-b pb-2">Aksi Dokumen</h4>
                <div class="flex flex-wrap gap-3">
                    
                    {{-- TOMBOL EDIT (Hanya jika Draft/Rejected) --}}
                    @if($evaluasi->canEdit())
                        <a href="{{ route('evaluasi.edit', $evaluasi->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition">
                            Edit Data
                        </a>
                    @endif

                    {{-- TOMBOL SUBMIT (Hanya jika Draft/Rejected) --}}
                    @if(in_array($evaluasi->status, ['draft', 'rejected']))
                        <form action="{{ route('evaluasi.submit', $evaluasi->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mensubmit data ini? Data tidak bisa diedit setelah disubmit.')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition">
                                Submit untuk Verifikasi
                            </button>
                        </form>
                    @endif

                    {{-- TOMBOL VERIFIKASI (Hanya jika Submitted & User GPM/Admin) --}}
                    @if($evaluasi->status == 'submitted' && (Auth::user()->isGPM() || Auth::user()->isAdmin()))
                        <form action="{{ route('evaluasi.verify', $evaluasi->id) }}" method="POST">
                            @csrf
                            @method('PATCH') {{-- PERBAIKAN UTAMA ADA DI SINI --}}
                            <button type="submit" onclick="return confirm('Verifikasi data ini?')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition">
                                Verifikasi
                            </button>
                        </form>

                        <button type="button" onclick="document.getElementById('rejectForm').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition">
                            Tolak / Revisi
                        </button>
                    @endif

                    {{-- TOMBOL APPROVE (Hanya jika Verified & User Dekan/Admin) --}}
                    @if($evaluasi->status == 'verified' && (Auth::user()->isDekan() || Auth::user()->isAdmin()))
                        <form action="{{ route('evaluasi.approve', $evaluasi->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Approve data ini?')" class="inline-flex items-center px-4 py-2 bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition">
                                Approve (Final)
                            </button>
                        </form>
                    @endif

                </div>

                {{-- Form Hidden untuk Reject (Muncul jika tombol Tolak diklik) --}}
                <div id="rejectForm" class="hidden mt-4 p-4 border border-red-200 bg-red-50 rounded-md">
                    <form action="{{ route('evaluasi.reject', $evaluasi->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <label class="block font-medium text-sm text-red-700 mb-2">Alasan Penolakan / Catatan Revisi:</label>
                        <textarea name="rejection_notes" rows="3" class="w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500" required></textarea>
                        <div class="mt-3 flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-bold uppercase rounded hover:bg-red-700">Kirim Penolakan</button>
                            <button type="button" onclick="document.getElementById('rejectForm').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 text-xs font-bold uppercase rounded hover:bg-gray-400">Batal</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>