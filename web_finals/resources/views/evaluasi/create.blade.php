<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Evaluasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('evaluasi.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Renstra -->
                            <div class="md:col-span-2">
                                <label for="renstra_id" class="block text-sm font-medium text-gray-700">Renstra *</label>
                                <select name="renstra_id" id="renstra_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Renstra</option>
                                    @foreach($renstras as $renstra)
                                    <option value="{{ $renstra->id }}" {{ old('renstra_id', request('renstra_id')) == $renstra->id ? 'selected' : '' }}>
                                        {{ $renstra->kode_renstra }} - {{ Str::limit($renstra->indikator, 60) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('renstra_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Prodi -->
                            <div>
                                <label for="prodi_id" class="block text-sm font-medium text-gray-700">Program Studi *</label>
                                <select name="prodi_id" id="prodi_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('prodi_id', Auth::user()->prodi_id) == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('prodi_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Target -->
                            <div>
                                <label for="target_id" class="block text-sm font-medium text-gray-700">Target *</label>
                                <select name="target_id" id="target_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Target</option>
                                    @foreach($targets as $target)
                                    <option value="{{ $target->id }}" {{ old('target_id') == $target->id ? 'selected' : '' }}>
                                        {{ $target->tahun }} - {{ $target->indikator?->kode_indikator }} (Target: {{ $target->target_value }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('target_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Semester -->
                            <div>
                                <label for="semester" class="block text-sm font-medium text-gray-700">Semester *</label>
                                <select name="semester" id="semester" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="genap" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                                @error('semester')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Tahun Evaluasi -->
                            <div>
                                <label for="tahun_evaluasi" class="block text-sm font-medium text-gray-700">Tahun Evaluasi *</label>
                                <input type="number" name="tahun_evaluasi" id="tahun_evaluasi" value="{{ old('tahun_evaluasi', date('Y')) }}" min="2020" max="2050" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('tahun_evaluasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Realisasi -->
                            <div>
                                <label for="realisasi" class="block text-sm font-medium text-gray-700">Realisasi *</label>
                                <input type="number" step="0.01" name="realisasi" id="realisasi" value="{{ old('realisasi') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('realisasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Ketercapaian -->
                            <div>
                                <label for="ketercapaian" class="block text-sm font-medium text-gray-700">Ketercapaian (%) *</label>
                                <input type="number" step="0.01" name="ketercapaian" id="ketercapaian" value="{{ old('ketercapaian') }}" min="0" max="200" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Masukkan persentase ketercapaian (0-200%)</p>
                                @error('ketercapaian')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Conditional Fields based on Ketercapaian -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg" id="achievement-fields">
                            <h4 class="text-sm font-medium text-gray-700 mb-4">Analisis Ketercapaian</h4>
                            
                            <!-- Faktor Pendukung (always shown) -->
                            <div class="mb-4">
                                <label for="faktor_pendukung" class="block text-sm font-medium text-gray-700">Faktor Pendukung</label>
                                <textarea name="faktor_pendukung" id="faktor_pendukung" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('faktor_pendukung') }}</textarea>
                                @error('faktor_pendukung')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Fields shown when ketercapaian < 100 -->
                            <div id="failure-fields" class="space-y-4" style="display: none;">
                                <div>
                                    <label for="akar_masalah" class="block text-sm font-medium text-gray-700">Akar Masalah *</label>
                                    <textarea name="akar_masalah" id="akar_masalah" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('akar_masalah') }}</textarea>
                                    @error('akar_masalah')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="faktor_penghambat" class="block text-sm font-medium text-gray-700">Faktor Penghambat *</label>
                                    <textarea name="faktor_penghambat" id="faktor_penghambat" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('faktor_penghambat') }}</textarea>
                                    @error('faktor_penghambat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mt-6">
                            <label for="bukti_file" class="block text-sm font-medium text-gray-700">Upload Bukti/Evidence</label>
                            <input type="file" name="bukti_file" id="bukti_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP (Max 10MB)</p>
                            @error('bukti_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Submit -->
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('evaluasi.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Simpan Evaluasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('ketercapaian').addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            const failureFields = document.getElementById('failure-fields');
            if (value < 100) {
                failureFields.style.display = 'block';
            } else {
                failureFields.style.display = 'none';
            }
        });
        // Trigger on page load
        document.getElementById('ketercapaian').dispatchEvent(new Event('input'));
    </script>
</x-app-layout>
