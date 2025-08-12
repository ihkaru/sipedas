<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ms" lang="ms">

<head>
    @php
        $c = now()->settings(['formatFunction' => 'translatedFormat']);
        $cons = App\Supports\Constants::class;
        $number = \Illuminate\Support\Number::class;
        $bil = \Terbilang::class;

        // --- INILAH PERBAIKAN UTAMA ---
        // Kelompokkan alokasi berdasarkan `surat_bast_id`.
        // Setiap grup akan menjadi satu lembar BAST yang unik.
        $alokasiPerBast = $alokasiHonor->groupBy('surat_bast_id');
    @endphp
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>BAST BPS MEMPAWAH</title>
    <meta name="author" content="BPS KABUPATEN MEMPAWAH" />
    {{-- (Tag <style> Anda tidak perlu diubah, biarkan seperti semula) --}}
    <style type="text/css">
        @media print {
            .pagebreak {
                page-break-before: always;
            }
        }

        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        h1 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: italic;
            font-weight: bold;
            text-decoration: none;
            font-size: 16pt;
        }

        h2 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: italic;
            font-weight: bold;
            text-decoration: none;
            font-size: 14pt;
        }

        .s2 {
            color: #00f;
            font-family: Calibri, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
        }

        .s3 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 8pt;
        }

        .s4 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: underline;
            font-size: 11pt;
        }

        .h3,
        h3 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        .p,
        p {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 11pt;
            margin: 0pt;
        }

        .s5 {
            color: #f00;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 11pt;
        }

        .s6 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
            vertical-align: 4pt;
        }

        li {
            display: block;
        }

        #l1 {
            padding-left: 0pt;
            counter-reset: c1 1;
        }

        #l1>li>*:first-child:before {
            counter-increment: c1;
            content: counter(c1, decimal) ". ";
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 11pt;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        #l2 {
            padding-left: 0pt;
            counter-reset: c2 1;
        }

        #l2>li>*:first-child:before {
            counter-increment: c2;
            content: counter(c2, lower-latin) ". ";
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 11pt;
        }

        #l2>li:first-child>*:first-child:before {
            counter-increment: c2 0;
        }
    </style>
</head>

<body>
    {{-- Loop baru ini mengulang untuk setiap BAST yang unik --}}
    @foreach ($alokasiPerBast as $suratBastId => $alokasiItems)
        @php
            // Ambil data pertama dari koleksi ini untuk mendapatkan info yang sama (mitra, bast, ppk).
            $firstAlokasi = $alokasiItems->first();
            if (!$firstAlokasi) {
                continue;
            } // Lewati jika grup kosong (untuk keamanan)

            $mitra = $firstAlokasi->mitra;
            $bast = $firstAlokasi->bast; // Ambil model NomorSurat BAST
            $tanggalSurat = $c::parse($bast->tanggal_nomor);

            $istilahJabatan = [
                'PML' => 'petugas pemeriksa lapangan',
                'PPL' => 'petugas pendataan lapangan',
                'PETUGAS ENTRI' => 'petugas pengolahan',
            ];
        @endphp

        {{-- Tambahkan pagebreak jika ini bukan BAST pertama yang dicetak --}}
        @if (!$loop->first)
            <div class="pagebreak"></div>
        @endif

        {{-- (KOP SURAT TIDAK BERUBAH) --}}
        <div style="display: flex;">
            <div style="max-width: 600px; display: flex">
                <div><span><img width=97 height=69 src="{{ asset('SPPD_files/Image_001.png') }}"></span></div>
                <div
                    style="text-indent: 0pt; line-height: 107%; text-align: left; align-self: center; padding-left: 10px;">
                    <h1 style="padding-bottom: 10pt;">BADAN PUSAT STATISTIK</h1>
                    <h1>KABUPATEN MEMPAWAH</h1>
                    <p class="s2" style="text-indent: 0pt; text-align: left; padding-top:10pt">
                        <a href="https://mempawahkab.bps.go.id/" class="s3" target="_blank">Jln. Raden Kusno No 59
                            Mempawah 78912 Telp (+62561)691049 Fax (+62561) 6695439 Homepage: </a>
                        <span
                            style="color: #00f; font-family: Calibri, sans-serif; font-style: normal; font-weight: normal; text-decoration: underline; font-size: 8pt;">https://mempawahkab.bps.go.id</span>
                        <a href="mailto:bps6104@bps.go.id" class="s3" target="_blank">E-mail: bps6104@bps.go.id</a>
                    </p>
                </div>
            </div>
        </div>

        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p class="s4" style="padding-top: 4pt; text-indent: 0pt; text-align: center;">
            <a name="bookmark0">BERITA ACARA SERAH TERIMA PEKERJAAN</a>
        </p>
        {{-- Gunakan data dari model BAST yang sudah diambil --}}
        <h3 style="text-indent: 0pt; text-align: center">Nomor : {{ $bast->nomor_surat_bast }}</h3>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="padding-left: 5pt; text-indent: 0pt; line-height: 150%; text-align: justify;">
            Pada hari ini, {{ $tanggalSurat->dayName }}, Tanggal {{ ucwords($bil::make($tanggalSurat->day)) }} Bulan
            {{ $tanggalSurat->monthName }} Tahun {{ ucwords($bil::make($tanggalSurat->year)) }}, kami yang bertanda
            tangan di bawah ini:
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>

        {{-- Data PIHAK PERTAMA (PPK) --}}
        <table style="padding-left: 50px;">
            <tr>
                <td style="width: 30px;">
                    <p>1.</p>
                </td>
                <td style="width: 100px;">
                    <p>Nama/NIP</p>
                </td>
                <td style="width: 20px;">
                    <p>:</p>
                </td>
                <td>
                    <p>{{ $ppk->nama }}/ NIP. {{ $ppk->nip }}</p>
                </td>
            </tr>
            <tr style="vertical-align: top;">
                <td></td>
                <td>
                    <p>Jabatan</p>
                </td>
                <td>
                    <p>:</p>
                </td>
                <td>
                    <p>Pejabat Pembuat Komitmen BPS Kabupaten Mempawah</p>
                </td>
            </tr>
            <tr style="vertical-align: top;">
                <td></td>
                <td>
                    <p>Alamat</p>
                </td>
                <td>
                    <p>:</p>
                </td>
                <td>
                    <p>Jl. Raden Kusno No. 59, RT. 001 / RW. 001 Kel. Terusan, Kec. Mempawah Hilir, Kab. Mempawah</p>
                </td>
            </tr>
        </table>
        <br>
        <p style="padding-left: 19pt; text-indent: 0pt; text-align: left">Dalam hal ini bertindak untuk dan atas nama
            Badan Pusat Statistik Kabupaten Mempawah, selanjutnya disebut <b>PIHAK PERTAMA</b>.</p>
        <br>

        {{-- Data PIHAK KEDUA (Mitra) --}}
        <table style="padding-left: 50px;">
            <tr style="vertical-align:top;">
                <td style="width: 30px;">
                    <p>2.</p>
                </td>
                <td style="width: 100px;">
                    <p>Nama</p>
                </td>
                <td style="width: 20px;">
                    <p>:</p>
                </td>
                <td>
                    <p>{{ $mitra->nama_1 }}</p>
                </td>
            </tr>
            <tr style="vertical-align: top;">
                <td></td>
                <td>
                    <p>Jabatan</p>
                </td>
                <td>
                    <p>:</p>
                </td>
                {{-- Jabatan bisa berbeda, ambil dari honor pertama di grup ini --}}
                <td>
                    <p>{{ ucwords($istilahJabatan[$firstAlokasi->honor->jabatan] ?? $firstAlokasi->honor->jabatan) }}
                    </p>
                </td>
            </tr>
            <tr style="vertical-align: top;">
                <td></td>
                <td>
                    <p>Alamat</p>
                </td>
                <td>
                    <p>:</p>
                </td>
                <td>
                    <p>Kecamatan {{ ucwords(strtolower($mitra->kecamatanName)) }}, Desa/Kelurahan
                        {{ ucwords(strtolower($mitra->desaName)) }}</p>
                </td>
            </tr>
        </table>
        <br>
        <p style="padding-left: 18pt; text-indent: 0pt; text-align: left">Dalam hal ini bertindak untuk dan atas nama
            dirinya sendiri, selanjutnya disebut <b>PIHAK KEDUA</b></p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="padding-left: 5pt; text-indent: 0pt; text-align: left">Menyatakan bahwa :</p>

        {{-- Detail Pekerjaan yang diselesaikan --}}
        <div style="padding-left: 40pt; text-align: justify;">
            <p style="text-indent: -18pt; line-height: 150%;">a. <b>PIHAK KEDUA</b> telah menyelesaikan pekerjaan
                {{-- Loop ini sekarang hanya akan menampilkan item untuk BAST ini --}}
                @foreach ($alokasiItems as $h)
                    {{ ucwords(strtolower($h->honor->jenis_honor)) }} pada kegiatan
                    {{ $h->honor->kegiatanManmit->nama }} sebanyak
                    {{ (int) $h->target_per_satuan_honor . ' ' . $h->honor->satuan_honor }}@if (!$loop->last)
                        ,
                    @endif
                @endforeach.
            </p>
            <p style="text-indent: -18pt; line-height: 150%;">b. <b>PIHAK PERTAMA</b> telah memeriksa dan menerima
                dengan baik hasil pekerjaan di Badan Pusat Statistik Kabupaten Mempawah dari <b>PIHAK KEDUA</b>.</p>
        </div>

        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="padding-left: 5pt; text-indent: 0pt; line-height: 151%; text-align: justify;">Demikian Berita Acara
            Serah Terima Pekerjaan ini dibuat agar dipergunakan sebagaimana mestinya.</p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="text-indent: 0pt; text-align: center">Mempawah, {{ $tanggalSurat->translatedFormat('d F Y') }}</p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>

        {{-- Tanda Tangan --}}
        <table style="border-collapse: collapse; margin-left: 74.7pt" cellspacing="0">
            <tr style="height: 79pt">
                <td style="width: 160pt; vertical-align: top; text-align: center;">
                    <p class="p"><b>PIHAK KEDUA</b>,</p>
                    <p style="text-indent: 0pt; text-align: left"><br><br><br><br></p>
                    <p class="p"><b>{{ $mitra->nama_1 }}</b></p>
                </td>
                <td style="width: 185pt; vertical-align: top; text-align: center;">
                    <p class="p"><b>PIHAK PERTAMA</b>,</p>
                    <p style="text-indent: 0pt; text-align: left"><br><br><br><br></p>
                    <p class="p"><b>{{ $ppk->nama }}</b></p>
                </td>
            </tr>
        </table>
        <br><br>
    @endforeach
</body>

</html>
