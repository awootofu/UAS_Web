<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Verifikasi Evaluasi
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            <div class="mb-4 flex justify-between items-center">
                <p class="text-gray-600">Kelola persetujuan dokumen evaluasi akademik (GPM & Dekan).</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renstra / Prodi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengaju</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($submissions as $evaluasi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $evaluasi->renstra->indikator ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $evaluasi->prodi->nama_prodi ?? 'Universitas' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $evaluasi->creator->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($evaluasi->status == 'submitted')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Perlu Verifikasi
                                        </span>
                                    @elseif($evaluasi->status == 'verified')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Verified - Perlu Approval
                                        </span>
                                    @elseif($evaluasi->status == 'approved')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-sm">
                                    @if($evaluasi->status == 'submitted' && in_array(auth()->user()->role, ['GPM', 'dekan']))
                                        <!-- GPM and Dekan can verify -->
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ route('verifications.verify', $evaluasi->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="verified">
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs" onclick="return confirm('Verifikasi evaluasi ini?')">
                                                    Verify
                                                </button>
                                            </form>
                                            <form action="{{ route('verifications.verify', $evaluasi->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs" onclick="return confirm('Tolak evaluasi ini?')">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($evaluasi->status == 'verified' && in_array(auth()->user()->role, ['GPM', 'dekan']))
                                        <!-- GPM and Dekan can approve -->
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ route('verifications.approve', $evaluasi->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="approved">
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs" onclick="return confirm('Approve evaluasi ini?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('verifications.approve', $evaluasi->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs" onclick="return confirm('Tolak evaluasi ini?')">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p>Tidak ada dokumen yang perlu diverifikasi saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <span class="text-xs text-gray-500">
                        Menampilkan {{ $submissions->count() }} data.
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>