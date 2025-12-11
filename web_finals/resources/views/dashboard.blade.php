<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }} - Renstra Evaluation System
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                    <p class="text-indigo-100">Role: {{ Auth::user()->role }} | {{ Auth::user()->prodi?->nama_prodi ?? 'Semua Prodi' }}</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Renstra -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Renstra</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_renstra'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Evaluasi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Evaluasi</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_evaluasi'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending RTL -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending RTL</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_rtl'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue RTL -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Overdue RTL</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['overdue_rtl'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-specific Stats -->
            @if(isset($stats['awaiting_verification']) || isset($stats['awaiting_approval']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                @if(isset($stats['awaiting_verification']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Menunggu Verifikasi</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['awaiting_verification'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($stats['awaiting_approval']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Menunggu Approval</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['awaiting_approval'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Quick Actions based on Role -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        @can('manage-renstra')
                        <a href="{{ route('renstra.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            + Tambah Renstra
                        </a>
                        @endcan

                        @can('kaprodi')
                        <a href="{{ route('evaluasi.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            + Input Evaluasi
                        </a>
                        @endcan

                        @can('gkm')
                        <a href="{{ route('rtl.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            + Buat RTL
                        </a>
                        @endcan

                        <a href="{{ route('renstra.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Lihat Semua Renstra
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Evaluasi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Evaluasi Terbaru</h3>
                        @if($recentEvaluasis->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentEvaluasis as $evaluasi)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $evaluasi->renstra?->kode_renstra ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $evaluasi->prodi?->nama_prodi }} - {{ ucfirst($evaluasi->semester) }} {{ $evaluasi->tahun_evaluasi }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($evaluasi->status == 'approved') bg-green-100 text-green-800
                                    @elseif($evaluasi->status == 'verified') bg-blue-100 text-blue-800
                                    @elseif($evaluasi->status == 'submitted') bg-yellow-100 text-yellow-800
                                    @elseif($evaluasi->status == 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($evaluasi->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        <a href="{{ route('evaluasi.index') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-800">Lihat semua &rarr;</a>
                        @else
                        <p class="text-gray-500 text-sm">Belum ada data evaluasi.</p>
                        @endif
                    </div>
                </div>

                <!-- Recent RTL -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">RTL Terbaru</h3>
                        @if($recentRTLs->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentRTLs as $rtl)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($rtl->rtl, 50) }}</p>
                                    <p class="text-xs text-gray-500">{{ $rtl->prodi?->nama_prodi }} - Deadline: {{ $rtl->deadline->format('d M Y') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($rtl->status == 'completed') bg-green-100 text-green-800
                                    @elseif($rtl->status == 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($rtl->isOverdue()) bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $rtl->isOverdue() ? 'Overdue' : ucfirst(str_replace('_', ' ', $rtl->status)) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        <a href="{{ route('rtl.index') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-800">Lihat semua &rarr;</a>
                        @else
                        <p class="text-gray-500 text-sm">Belum ada data RTL.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
