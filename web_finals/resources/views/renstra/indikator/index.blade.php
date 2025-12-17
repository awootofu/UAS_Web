<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Indikator Renstra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Sukses --}}
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
            @endif

            {{-- Alert Error --}}
            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Form Tambah --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <h3 class="font-bold mb-4">Tambah Indikator Baru</h3>
                <form action="{{ route('renstra.indikator.store') }}" method="POST" class="flex flex-wrap md:flex-nowrap gap-4 items-start md:items-end">
                    @csrf
                    
                    {{-- Input Kode Indikator --}}
                    <div class="w-full md:w-1/5">
                        <label class="block text-sm font-medium text-gray-700">Kode</label>
                        <input type="text" name="kode_indikator" value="{{ old('kode_indikator') }}" placeholder="Cth: IND-01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    {{-- Input Kegiatan (Parent) --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700">Kegiatan</label>
                        <select name="kegiatan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            @foreach($kegiatans as $keg)
                                <option value="{{ $keg->id }}" {{ old('kegiatan_id') == $keg->id ? 'selected' : '' }}>
                                    {{ $keg->kode_kegiatan }} - {{ Str::limit($keg->nama_kegiatan, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Nama Indikator --}}
                    <div class="w-full md:w-1/2">
                        <label class="block text-sm font-medium text-gray-700">Indikator</label>
                        <input type="text" name="indikator" value="{{ old('indikator') }}" placeholder="Deskripsi Indikator..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-0.5 h-10">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>

            {{-- Tabel Data --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikator</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($indikators as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ $item->kode_indikator }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ optional($item->kegiatan)->nama_kegiatan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ Str::limit($item->indikator, 80) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('renstra.indikator.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada indikator.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</x-app-layout>