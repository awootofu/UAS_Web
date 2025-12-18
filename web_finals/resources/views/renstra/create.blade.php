<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Renstra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Info Box -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm text-blue-800">
                            <strong>Catatan:</strong> Kode Renstra akan di-generate otomatis dengan format RENS-YYYY-NNN (contoh: RENS-2025-001)
                        </p>
                    </div>

                    <form method="POST" action="{{ route('renstra.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Periode Akademik -->
                            <div>
                                <label for="periode" class="block text-sm font-medium text-gray-700">Periode Akademik *</label>
                                <select name="periode" id="periode" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach($periodes as $periode)
                                    <option value="{{ $periode }}" {{ old('periode') == $periode ? 'selected' : '' }}>
                                        {{ $periode }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('periode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori *</label>
                                <input type="text" name="kategori" id="kategori" value="{{ old('kategori') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Masukkan kategori renstra">
                                @error('kategori')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kegiatan -->
                            <div>
                                <label for="kegiatan" class="block text-sm font-medium text-gray-700">Kegiatan *</label>
                                <input type="text" name="kegiatan" id="kegiatan" value="{{ old('kegiatan') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Masukkan kegiatan renstra">
                                @error('kegiatan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Indikator Value -->
                            <div>
                                <label for="indikator_value" class="block text-sm font-medium text-gray-700">Nilai Indikator *</label>
                                <input type="number" step="0.01" name="indikator_value" id="indikator_value" value="{{ old('indikator_value') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Masukkan nilai indikator">
                                @error('indikator_value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Prodi -->
                            <div>
                                <label for="prodi_id" class="block text-sm font-medium text-gray-700">Program Studi</label>
                                <select name="prodi_id" id="prodi_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Prodi (Universitas)</option>
                                    @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('prodi_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Indikator Description -->
                        <div class="mt-6">
                            <label for="indikator" class="block text-sm font-medium text-gray-700">Deskripsi Indikator *</label>
                            <textarea name="indikator" id="indikator" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('indikator') }}</textarea>
                            @error('indikator')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mt-6">
                            <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('renstra.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Simpan Renstra
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
