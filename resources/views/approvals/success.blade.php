<!DOCTYPE html>
<html>

<head>
    <title>Persetujuan Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card text-center shadow-sm">
            <div class="card-header bg-success text-white">
                <h3>Aksi Berhasil Diproses!</h3>
            </div>
            <div class="card-body">
                <h5 class="card-title">Pengajuan Telah Disetujui</h5>
                <p class="card-text">
                    Pengajuan dengan uraian "<strong>{{ $pengajuan->uraian_pengajuan }}</strong>" telah berhasil Anda
                    setujui tanpa catatan.
                </p>
                <p>Proses telah dilanjutkan ke tahap berikutnya. Anda bisa menutup halaman ini.</p>
            </div>
            <div class="card-footer text-muted">
                {{ now()->format('d M Y H:i') }}
            </div>
        </div>
    </div>
</body>

</html>
