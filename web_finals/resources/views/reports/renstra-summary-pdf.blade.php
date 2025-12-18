<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Renstra</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; font-size: 18px; }
        h2 { font-size: 14px; margin-top: 20px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .header { text-align: center; margin-bottom: 20px; }
        .meta { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN RENCANA STRATEGIS</h1>
        <p class="meta">Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p class="meta">Filter Tahun: {{ $tahun }}</p>
    </div>

    @forelse ($renstras as $kategori => $items)
        <h2>{{ $kategori ?? 'Tanpa Kategori' }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%">Kode</th>
                    <th style="width: 30%">Kegiatan</th>
                    <th style="width: 15%">Prodi</th>
                    <th style="width: 10%">Periode</th>
                    <th style="width: 15%">Indikator</th>
                    <th style="width: 10%">Target</th>
                    <th style="width: 10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $renstra)
                    <tr>
                        <td>{{ $renstra->kode_renstra }}</td>
                        <td>{{ $renstra->kegiatan?->nama_kegiatan ?? '-' }}</td>
                        <td>{{ $renstra->prodi?->nama_prodi ?? 'Universitas' }}</td>
                        <td>{{ $renstra->tahun_awal }} - {{ $renstra->tahun_akhir }}</td>
                        <td>{{ $renstra->indikatorRelation?->nama_indikator ?? '-' }}</td>
                        <td>{{ $renstra->target?->target_value ?? '-' }} {{ $renstra->target?->satuan ?? '' }}</td>
                        <td>{{ ucfirst($renstra->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p style="text-align: center; color: #666;">Tidak ada data renstra.</p>
    @endforelse
</body>
</html>
