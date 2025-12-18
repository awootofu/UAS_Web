<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Buat RTL') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('rtl.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Evaluasi -->
                            <div class="md:col-span-2">
                                <label for="evaluasi_id" class="block text-sm font-medium text-gray-700">Evaluasi (Tidak Tercapai) *</label>
                                <select name="evaluasi_id" id="evaluasi_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Pilih Evaluasi</option>
                                    @foreach($evaluasis as $evaluasi)
                                    <option value="{{ $evaluasi->id }}" {{ old('evaluasi_id', request('evaluasi_id')) == $evaluasi->id ? 'selected' : '' }}>
                                        {{ $evaluasi->renstra?->kode_renstra }} - {{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }} (Ketercapaian: {{ number_format($evaluasi->ketercapaian, 2) }}%)
                                    </option>
                                    @endforeach
                                </select>
                                @error('evaluasi_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Prodi -->
                            <div>
                                <label for="prodi_id" class="block text-sm font-medium text-gray-700">Program Studi *</label>
                                <select name="prodi_id" id="prodi_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('prodi_id', Auth::user()->prodi_id) == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                                    @endforeach
                                </select>
                                @error('prodi_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- PIC -->
                            <div>
                                <label for="pic_rtl" class="block text-sm font-medium text-gray-700">Person In Charge (PIC) *</label>
                                <input type="text" name="pic_rtl" id="pic_rtl" value="{{ old('pic_rtl') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('pic_rtl')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Deadline -->
                            <div>
                                <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline *</label>
                                <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('deadline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- RTL Description -->
                        <div class="mt-6">
                            <label for="rtl" class="block text-sm font-medium text-gray-700">Rencana Tindak Lanjut *</label>
                            <textarea name="rtl" id="rtl" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('rtl') }}</textarea>
                            @error('rtl')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mt-6">
                            <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('keterangan') }}</textarea>
                            @error('keterangan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- File Upload -->
                        <div class="mt-6">
                            <label for="bukti_file" class="block text-sm font-medium text-gray-700">Upload Bukti (Opsional)</label>
                            <input type="file" name="bukti_file" id="bukti_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP (Max 10MB)</p>
                            @error('bukti_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('rtl.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan RTL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
