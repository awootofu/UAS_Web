<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail RTL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Rencana Tindak Lanjut</h3>
                            <p class="text-gray-500">{{ $rtl->evaluasi?->renstra?->kode_renstra }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($rtl->status == 'completed') bg-green-100 text-green-800
                            @elseif($rtl->status == 'in_progress') bg-blue-100 text-blue-800
                            @elseif($rtl->isOverdue()) bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $rtl->isOverdue() && $rtl->status != 'completed' ? 'Overdue' : ucfirst(str_replace('_', ' ', $rtl->status)) }}
                        </span>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500">RTL</h4>
                        <p class="mt-1 text-gray-900">{{ $rtl->rtl }}</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Prodi</h4>
                            <p class="mt-1 text-gray-900">{{ $rtl->prodi?->nama_prodi }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">PIC</h4>
                            <p class="mt-1 text-gray-900">{{ $rtl->pic_rtl }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Deadline</h4>
                            <p class="mt-1 {{ $rtl->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900' }}">{{ $rtl->deadline->format('d M Y') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Dibuat</h4>
                            <p class="mt-1 text-gray-900">{{ $rtl->created_at->format('d M Y') }}</p>
                        </div>
                    </div>

                    @if($rtl->keterangan)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500">Keterangan</h4>
                        <p class="mt-1 text-gray-900">{{ $rtl->keterangan }}</p>
                    </div>
                    @endif

                    @if($rtl->bukti_rtl)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500">Bukti RTL</h4>
                        <a href="{{ Storage::url($rtl->bukti_rtl) }}" target="_blank" class="mt-1 inline-flex items-center text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Download Bukti
                        </a>
                    </div>
                    @endif

                    @if($rtl->verifier)
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500">Diverifikasi oleh: {{ $rtl->verifier->name }} pada {{ $rtl->verified_at?->format('d M Y H:i') }}</p>
                        @if($rtl->verification_notes)
                        <p class="text-sm text-gray-500">Catatan: {{ $rtl->verification_notes }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                    <div class="flex flex-wrap gap-3">
                        @if($rtl->canEdit() && (Auth::user()->isAdmin() || (Auth::user()->isGKM() && Auth::user()->prodi_id == $rtl->prodi_id)))
                        <a href="{{ route('rtl.edit', $rtl) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Edit</a>
                        
                        @if($rtl->status == 'pending')
                        <form action="{{ route('rtl.start-progress', $rtl) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Mulai Pengerjaan</button>
                        </form>
                        @endif

                        @if($rtl->canComplete())
                        <button onclick="document.getElementById('complete-modal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Selesaikan RTL
                        </button>
                        @endif
                        @endif

                        @can('verify', $rtl)
                        @if($rtl->status == 'completed' && !$rtl->verified_at)
                        <form action="{{ route('rtl.verify', $rtl) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Verifikasi</button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>

            <a href="{{ route('rtl.index') }}" class="text-indigo-600 hover:text-indigo-800">&larr; Kembali ke Daftar RTL</a>
        </div>
    </div>

    <!-- Complete Modal -->
    <div id="complete-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Selesaikan RTL</h3>
            <form action="{{ route('rtl.complete', $rtl) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Penyelesaian *</label>
                    <input type="file" name="bukti_file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" class="w-full text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
