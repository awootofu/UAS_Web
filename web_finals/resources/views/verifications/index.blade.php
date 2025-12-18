<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen - GPM & Dekan</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <nav class="bg-white text-blue-900 p-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="text-xl font-bold tracking-wide">Sistem Evaluasi Kampus</h1>
            </div>
            <div class="text-sm font-medium">
                <i class="fas fa-user-circle mr-1"></i>
                Halo, {{ Auth::user()->name ?? 'Guest' }} 
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded ml-2">
                    {{ Auth::user()->role ?? 'User' }}
                </span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center" role="alert">
                <div>
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Daftar Verifikasi</h2>
                <p class="text-gray-500 text-sm mt-1">
                    Kelola persetujuan dokumen evaluasi akademik (GPM & Dekan).
                </p>
            </div>

            <a href="{{ url('/dashboard') }}" class="group bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-5 rounded-lg shadow transition-all duration-200 flex items-center gap-2">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dokumen / Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengaju (Submitter)</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi / Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($submissions as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 font-bold text-sm">{{ $item->title }}</span>
                                    <span class="text-gray-400 text-xs mt-1">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                                        <i class="fas fa-user text-xs"></i>
                                    </div>
                                    <span>{{ $item->submitted_by }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($item->status == 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                                        Perlu Verifikasi
                                    </span>
                                @elseif($item->status == 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                        <i class="fas fa-times-circle mr-1"></i> Rejected
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($item->status == 'pending')
                                    @can('verify', $item)
                                        <div class="flex justify-center gap-3">
                                            <form action="{{ route('verifications.update', $item->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="approved">
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow-sm transition-transform hover:scale-105 tooltip" title="Setujui" onclick="return confirm('Apakah Anda yakin ingin menyetujui dokumen ini?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>

                                            <form action="{{ route('verifications.update', $item->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-sm transition-transform hover:scale-105 tooltip" title="Tolak" onclick="return confirm('Apakah Anda yakin ingin menolak dokumen ini?')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs italic flex flex-col items-center">
                                            <i class="fas fa-lock mb-1"></i>
                                            Menunggu GPM/Dekan
                                        </span>
                                    @endcan

                                @else
                                    <div class="text-xs text-gray-500 border-l-2 border-gray-300 pl-3 text-left inline-block">
                                        <p class="font-semibold text-gray-700">
                                            <i class="fas fa-user-check mr-1"></i>
                                            {{ $item->verifier->name ?? 'ID: '.$item->verifier_id }}
                                        </p>
                                        <p class="mt-0.5">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($item->verified_at)->format('d M Y, H:i') }} WIB
                                        </p>
                                    </div>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="far fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                    <p>Tidak ada dokumen yang perlu diverifikasi saat ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
                <span class="text-xs text-gray-500">
                    Menampilkan {{ count($submissions) }} data.
                </span>
            </div>
        </div>

    </div>

</body>
</html>