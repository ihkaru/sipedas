<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Persetujuan Surat Tugas' }} — Dokter V</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            color: #f1f5f9;
        }
        .card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            max-width: 32rem;
            width: 100%;
            padding: 2rem 1.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .icon-wrapper {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .icon-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 2px solid rgba(16, 185, 129, 0.3);
        }
        .icon-already {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 2px solid rgba(59, 130, 246, 0.3);
        }
        .icon-error {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }
        .badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }
        .badge-success { background: #065f46; color: #a7f3d0; }
        .badge-info { background: #1e40af; color: #bfdbfe; }
        .badge-danger { background: #991b1b; color: #fecaca; }
        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }
        .desc {
            font-size: 0.925rem;
            color: #94a3b8;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }
        .details-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 0.875rem;
            padding: 1.125rem;
            text-align: left;
            margin-bottom: 1.75rem;
            font-size: 0.875rem;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #64748b;
            font-weight: 500;
        }
        .details-value {
            color: #e2e8f0;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.925rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
        }
        .btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .footer-note {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        @if ($status === 'success')
            <div class="icon-wrapper icon-success">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div><span class="badge badge-success">Disetujui Langsung</span></div>
        @elseif ($status === 'already_approved')
            <div class="icon-wrapper icon-already">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div><span class="badge badge-info">Sudah Disetujui</span></div>
        @else
            <div class="icon-wrapper icon-error">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div><span class="badge badge-danger">Gagal</span></div>
        @endif

        <h1>{{ $title }}</h1>
        <p class="desc">{{ $message }}</p>

        @if (isset($first))
            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">No. Surat Tugas</span>
                    <span class="details-value" style="color: #38bdf8;">{{ $nomorSurat ?? ($first->suratTugas?->nomor_surat_tugas ?? '-') }}</span>
                </div>
                @if (!empty($nomorSpd ?? $first->suratPerjadin?->nomor_surat_perjadin))
                    <div class="details-row">
                        <span class="details-label">No. SPD</span>
                        <span class="details-value">{{ $nomorSpd ?? $first->suratPerjadin->nomor_surat_perjadin }}</span>
                    </div>
                @endif
                <div class="details-row">
                    <span class="details-label">Kegiatan</span>
                    <span class="details-value">{{ $first->kegiatan?->nama ?? '-' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Tujuan</span>
                    <span class="details-value">{{ $first->nama_tempat_tujuan ?? $first->tujuan_penugasan ?? '-' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Tertugas</span>
                    <span class="details-value">
                        @if ($penugasans && $penugasans->count() > 1)
                            {{ $penugasans->count() }} orang ({{ $first->pegawai?->nama ?? $first->mitra?->nama_1 ?? 'Tim' }} dkk)
                        @else
                            {{ $first->pegawai?->nama ?? $first->mitra?->nama_1 ?? '-' }}
                        @endif
                    </span>
                </div>
                @if (isset($approverLabel))
                    <div class="details-row">
                        <span class="details-label">Penyetuju</span>
                        <span class="details-value" style="color: #a7f3d0;">{{ $approverLabel }}</span>
                    </div>
                @endif
            </div>
        @endif

        <div class="btn-group">
            <a href="{{ url('/a/penugasans') }}" class="btn btn-primary">
                Buka di Dashboard Dokter V
            </a>
            @if (isset($first) && $first->surat_tugas_id)
                <a href="{{ route('cetak.penugasan', ['id' => $first->id]) }}" target="_blank" class="btn btn-secondary">
                    Lihat Dokumen Surat Tugas (PDF)
                </a>
            @endif
        </div>

        <p class="footer-note">Dokter V — Sistem Tata Kelola Penugasan & Administrasi Naskah Dinas BPS</p>
    </div>
</body>
</html>
