<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Evaluasi') }}
            </h2>
            <a href="{{ route('evaluasi.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-xs font-semibold uppercase hover:bg-gray-600 transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('evaluasi.update', $evaluasi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Tahun Evaluasi</label>
                                <input type="number" name="tahun_evaluasi" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tahun_evaluasi') border-red-500 @enderror" 
                                    value="{{ old('tahun_evaluasi', $evaluasi->tahun_evaluasi) }}" required min="2020" max="2050">
                                @error('tahun_evaluasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Semester</label>
                                <select name="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('semester') border-red-500 @enderror" required>
                                    <option value="ganjil" {{ old('semester', $evaluasi->semester) == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="genap" {{ old('semester', $evaluasi->semester) == 'genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                                @error('semester') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Program Studi</label>
                                <select name="prodi_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('prodi_id') border-red-500 @enderror" required>
                                    <option value="">Pilih Prodi</option>
                                    @foreach($prodis as $prodi)
                                        <option value="{{ $prodi->id }}" {{ old('prodi_id', $evaluasi->prodi_id) == $prodi->id ? 'selected' : '' }}>
                                            {{ $prodi->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('prodi_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Item Renstra</label>
                                <select name="renstra_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('renstra_id') border-red-500 @enderror" required>
                                    <option value="">Pilih Renstra</option>
                                    @foreach($renstras as $renstra)
                                        <option value="{{ $renstra->id }}" {{ old('renstra_id', $evaluasi->renstra_id) == $renstra->id ? 'selected' : '' }}>
                                            {{ $renstra->kode ?? $renstra->id }} - {{ Str::limit($renstra->deskripsi ?? 'Renstra Item', 100) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('renstra_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Target Renstra</label>
                                <select name="target_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('target_id') border-red-500 @enderror" required>
                                    <option value="">Pilih Target</option>
                                    @foreach($targets as $target)
                                        <option value="{{ $target->id }}" {{ old('target_id', $evaluasi->target_id) == $target->id ? 'selected' : '' }}>
                                            {{ $target->nilai_target }} ({{ $target->tahun }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Realisasi</label>
                                <input type="number" step="0.01" name="realisasi" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('realisasi') border-red-500 @enderror" 
                                    value="{{ old('realisasi', $evaluasi->realisasi) }}" required>
                                @error('realisasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Ketercapaian (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="ketercapaian" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ketercapaian') border-red-500 @enderror" 
                                        value="{{ old('ketercapaian', $evaluasi->ketercapaian) }}" required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                @error('ketercapaian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700 mb-1">Faktor Pendukung (Jika Tercapai)</label>
                                <textarea name="faktor_pendukung" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('faktor_pendukung', $evaluasi->faktor_pendukung) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 mb-1">Akar Masalah (Jika < 100%)</label>
                                    <textarea name="akar_masalah" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('akar_masalah', $evaluasi->akar_masalah) }}</textarea>
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700 mb-1">Faktor Penghambat (Jika < 100%)</label>
                                    <textarea name="faktor_penghambat" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('faktor_penghambat', $evaluasi->faktor_penghambat) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-1">Bukti Dokumen</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition">
                                <div class="space-y-1 text-center w-full">
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="bukti_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                            <span>Upload file baru</span>
                                            <input id="bukti_file" name="bukti_file" type="file" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PDF, DOC, JPG up to 10MB</p>
                                    @if($evaluasi->bukti)
                                        <div class="mt-3 text-sm text-gray-900 bg-blue-50 p-2 rounded inline-block">
                                            File saat ini: 
                                            <a href="{{ Storage::url($evaluasi->bukti->file_path) }}" target="_blank" class="font-bold text-blue-600 hover:underline">
                                                {{ $evaluasi->bukti->nama_file }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @error('bukti_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-6">
                            <a href="{{ route('evaluasi.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Batal</a>
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>