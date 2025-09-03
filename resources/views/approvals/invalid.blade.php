<!DOCTYPE html>
<html>

<head>
    <title>Aksi Gagal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card text-center shadow-sm">
            <div class="card-header bg-danger text-white">
                <h3>Aksi Tidak Dapat Diproses</h3>
            </div>
            <div class="card-body">
                <p class="card-text">
                    <strong>{{ $message }}</strong>
                </p>
                <p>Silakan proses pengajuan ini secara manual melalui aplikasi DOKTER-V.</p>
            </div>
            <div class="card-footer text-muted">
                {{ now()->format('d M Y H:i') }}
            </div>
        </div>
    </div>
</body>

</html>
