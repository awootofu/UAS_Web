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
                    <form method="POST" action="{{ route('renstra.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kode Renstra -->
                            <div>
                                <label for="kode_renstra" class="block text-sm font-medium text-gray-700">Kode Renstra *</label>
                                <input type="text" name="kode_renstra" id="kode_renstra" value="{{ old('kode_renstra') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('kode_renstra')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label for="kategori_id" class="block text-sm font-medium text-gray-700">Kategori *</label>
                                <select name="kategori_id" id="kategori_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kegiatan -->
                            <div>
                                <label for="kegiatan_id" class="block text-sm font-medium text-gray-700">Kegiatan *</label>
                                <select name="kegiatan_id" id="kegiatan_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kegiatan</option>
                                    @foreach($kegiatans as $kegiatan)
                                    <option value="{{ $kegiatan->id }}" {{ old('kegiatan_id') == $kegiatan->id ? 'selected' : '' }}>
                                        {{ $kegiatan->nama_kegiatan }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('kegiatan_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Indikator Referensi -->
                            <div>
                                <label for="indikator_id" class="block text-sm font-medium text-gray-700">Indikator Referensi *</label>
                                <select name="indikator_id" id="indikator_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Indikator</option>
                                    @foreach($indikators as $indikator)
                                    <option value="{{ $indikator->id }}" {{ old('indikator_id') == $indikator->id ? 'selected' : '' }}>
                                        {{ $indikator->kode_indikator }} - {{ Str::limit($indikator->nama_indikator, 50) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('indikator_id')
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

                            <!-- Tahun Awal -->
                            <div>
                                <label for="tahun_awal" class="block text-sm font-medium text-gray-700">Tahun Awal *</label>
                                <input type="number" name="tahun_awal" id="tahun_awal" value="{{ old('tahun_awal', date('Y')) }}" min="2020" max="2050" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('tahun_awal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tahun Akhir -->
                            <div>
                                <label for="tahun_akhir" class="block text-sm font-medium text-gray-700">Tahun Akhir *</label>
                                <input type="number" name="tahun_akhir" id="tahun_akhir" value="{{ old('tahun_akhir', date('Y') + 4) }}" min="2020" max="2050" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('tahun_akhir')
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
