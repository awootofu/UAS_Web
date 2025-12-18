<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Renstra {{ $periode ?? date('Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        .header h2 {
            font-size: 14px;
            margin: 0;
            font-weight: normal;
        }
        .meta {
            margin-bottom: 20px;
        }
        .meta table {
            width: 100%;
        }
        .meta td {
            padding: 3px 0;
        }
        .meta .label {
            width: 120px;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        table.data th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .status-achieved { color: #059669; font-weight: bold; }
        .status-not-achieved { color: #dc2626; font-weight: bold; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px;
            background-color: #e5e7eb;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin-top: 60px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
        .summary-box {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 20px;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
        }
        .summary-stats {
            display: flex;
        }
        .stat-item {
            margin-right: 30px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN EVALUASI RENSTRA</h1>
        <h2>Periode {{ $periode ?? date('Y') }}</h2>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Program Studi</td>
                <td>: {{ $prodi->nama_prodi ?? 'Semua Program Studi' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ now()->format('d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Dicetak Oleh</td>
                <td>: {{ auth()->user()->name }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <h3>Ringkasan</h3>
        <table>
            <tr>
                <td width="25%">Total Renstra: <strong>{{ $renstras->count() }}</strong></td>
                <td width="25%">Tercapai: <strong class="status-achieved">{{ $renstras->where('status', 'achieved')->count() }}</strong></td>
                <td width="25%">Tidak Tercapai: <strong class="status-not-achieved">{{ $renstras->where('status', 'not_achieved')->count() }}</strong></td>
                <td width="25%">Pending: <strong>{{ $renstras->where('status', 'pending')->count() }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section-title">DATA RENSTRA</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Kode</th>
                <th width="23%">Indikator</th>
                <th width="10%">Target</th>
                <th width="10%">Capaian</th>
                <th width="15%">Status</th>
                <th width="25%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($renstras as $index => $renstra)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $renstra->kode_renstra }}</td>
                <td>{{ $renstra->indikator?->nama_indikator ?? '-' }}</td>
                <td>{{ $renstra->target?->target_value ?? '-' }}</td>
                <td>{{ $renstra->capaian ?? '-' }}</td>
                <td>
                    @if($renstra->status == 'achieved')
                    <span class="status-achieved">✓ Tercapai</span>
                    @elseif($renstra->status == 'not_achieved')
                    <span class="status-not-achieved">✗ Tidak Tercapai</span>
                    @else
                    Pending
                    @endif
                </td>
                <td>{{ $renstra->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data renstra</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($evaluasis->isNotEmpty())
    <div class="page-break"></div>
    <div class="section-title">DATA EVALUASI</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Renstra</th>
                <th width="10%">Periode</th>
                <th width="10%">Capaian</th>
                <th width="15%">Status</th>
                <th width="20%">Analisis</th>
                <th width="25%">Catatan GPM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluasis as $index => $evaluasi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $evaluasi->renstra?->kode_renstra }}</td>
                <td>{{ $evaluasi->periode_evaluasi }}</td>
                <td>{{ $evaluasi->nilai_capaian }}</td>
                <td>
                    @if($evaluasi->status == 'approved')
                    <span class="status-achieved">Disetujui</span>
                    @elseif($evaluasi->status == 'rejected')
                    <span class="status-not-achieved">Ditolak</span>
                    @else
                    Pending
                    @endif
                </td>
                <td>{{ Str::limit($evaluasi->analisis_gap, 50) }}</td>
                <td>{{ Str::limit($evaluasi->catatan_gpm, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($rtls->isNotEmpty())
    <div class="section-title">RENCANA TINDAK LANJUT (RTL)</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Renstra</th>
                <th width="30%">RTL</th>
                <th width="15%">PIC</th>
                <th width="15%">Deadline</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rtls as $index => $rtl)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $rtl->evaluasi?->renstra?->kode_renstra }}</td>
                <td>{{ Str::limit($rtl->rtl, 80) }}</td>
                <td>{{ $rtl->pic_rtl }}</td>
                <td>{{ $rtl->deadline?->format('d/m/Y') }}</td>
                <td>
                    @if($rtl->status == 'completed')
                    <span class="status-achieved">Selesai</span>
                    @elseif($rtl->status == 'in_progress')
                    In Progress
                    @else
                    Pending
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <div class="signature">
            <p>Mengetahui,</p>
            <div class="signature-line">
                <p>(_______________________)</p>
                <p>{{ $prodi ? 'Kaprodi ' . $prodi->nama_prodi : 'Dekan' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
