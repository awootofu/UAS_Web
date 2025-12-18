<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Target Renstra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
            @endif

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
                <h3 class="font-bold mb-4">Tambah Target Tahunan</h3>
                <form action="{{ route('renstra.target.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    @csrf
                    
                    {{-- Input Indikator --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Indikator</label>
                        <select name="indikator_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Indikator --</option>
                            @foreach($indikators as $ind)
                                <option value="{{ $ind->id }}" {{ old('indikator_id') == $ind->id ? 'selected' : '' }}>
                                    {{ $ind->kode_indikator }} - {{ Str::limit($ind->nama_indikator, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Tahun --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <input type="number" name="tahun" value="{{ old('tahun', date('Y')) }}" min="2020" max="2030" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    {{-- Input Nilai Target (PERBAIKAN NAME) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nilai Target</label>
                        <input type="text" name="target_value" value="{{ old('target_value') }}" placeholder="Contoh: 100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    {{-- Input Satuan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Satuan</label>
                        <input type="text" name="satuan" value="{{ old('satuan') }}" placeholder="%, Dokumen" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="md:col-span-5 text-right mt-2">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">Simpan Target</button>
                    </div>
                </form>
            </div>

            {{-- Tabel Data --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indikator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($targets as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="font-bold text-xs bg-gray-100 px-2 py-1 rounded border">{{ $item->indikator->kode_indikator ?? '-' }}</span>
                                <span class="ml-2">{{ Str::limit($item->indikator->nama_indikator ?? '-', 60) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ $item->tahun }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{-- PERBAIKAN DISPLAY --}}
                                {{ $item->target_value }} <span class="text-gray-500 text-xs">{{ $item->satuan }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('renstra.target.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada target yang diatur.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</x-app-layout>