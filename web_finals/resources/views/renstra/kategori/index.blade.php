<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kategori Renstra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. TAMBAHAN: Menampilkan Pesan Sukses --}}
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            {{-- 2. TAMBAHAN: Menampilkan Error Umum (Opsional) --}}
            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <h3 class="font-bold mb-4">Tambah Kategori Baru</h3>
                <form action="{{ route('renstra.kategori.store') }}" method="POST" class="flex flex-wrap md:flex-nowrap gap-4 items-start md:items-end">
                    @csrf
                    
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700">Kode</label>
                        {{-- 3. PERBAIKAN: Tambah value="{{ old() }}" dan class error --}}
                        <input type="text" name="kode_kategori" 
                               value="{{ old('kode_kategori') }}"
                               placeholder="Cth: K-01" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('kode_kategori') border-red-500 @enderror" 
                               required>
                        {{-- 4. PERBAIKAN: Pesan Error di bawah input --}}
                        @error('kode_kategori')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-1/2">
                        <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                        <input type="text" name="nama_kategori" 
                               value="{{ old('nama_kategori') }}"
                               placeholder="Nama Kategori..." 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('nama_kategori') border-red-500 @enderror" 
                               required>
                        @error('nama_kategori')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-0.5 h-10">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($kategoris as $kategori)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ $kategori->kode_kategori }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $kategori->nama_kategori }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('renstra.kategori.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada kategori.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</x-app-layout>