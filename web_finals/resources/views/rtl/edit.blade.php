<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit RTL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('rtl.update', $rtl) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Evaluasi</label>
                            <p class="mt-1 text-gray-900">{{ $rtl->evaluasi?->renstra?->kode_renstra }} - {{ $rtl->evaluasi?->renstra?->indikator?->nama_indikator }}</p>
                            <input type="hidden" name="evaluasi_id" value="{{ $rtl->evaluasi_id }}">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Rencana Tindak Lanjut *</label>
                            <textarea name="rtl" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('rtl', $rtl->rtl) }}</textarea>
                            @error('rtl') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deadline *</label>
                                <input type="date" name="deadline" value="{{ old('deadline', $rtl->deadline->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('deadline') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">PIC *</label>
                                <input type="text" name="pic_rtl" value="{{ old('pic_rtl', $rtl->pic_rtl) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('pic_rtl') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending" {{ $rtl->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $rtl->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                @if(Auth::user()->isAdmin())
                                <option value="completed" {{ $rtl->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea name="keterangan" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('keterangan', $rtl->keterangan) }}</textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Upload Bukti RTL</label>
                            @if($rtl->bukti_rtl)
                            <p class="text-sm text-gray-500 mb-2">Bukti saat ini: <a href="{{ Storage::url($rtl->bukti_rtl) }}" target="_blank" class="text-indigo-600 hover:underline">Lihat Bukti</a></p>
                            @endif
                            <input type="file" name="bukti_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-sm text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah bukti. Format: PDF, DOC, XLS, JPG, PNG, ZIP (max 10MB)</p>
                            @error('bukti_file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('rtl.show', $rtl) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
