<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ms" lang="ms">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PERJANJIAN KERJA</title>
    <meta name="author" content="Direktorat Statistik Kesejahteraan Rakyat" />
    @php
        // Persiapan variabel helper
        $c = now()->setLocale('id_ID');
        $c = now()->settings(['formatFunction' => 'translatedFormat']);
        $cons = App\Supports\Constants::class;
        $number = \Illuminate\Support\Number::class;
        $bil = \Terbilang::class;

        // LOGIKA BARU: Mengambil ID Mitra unik dari koleksi yang sudah difilter oleh Controller
        $idSobatUnik = $alokasiHonor->pluck('mitra.id_sobat')->unique();
    @endphp
    <style type="text/css">
        @media print {
            .pagebreak {
                page-break-before: always;
            }

            /* page-break-after works, as well */
        }

        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        h1 {
            color: black;
            font-family: "Bookman Old Style", serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .p,
        p {
            color: black;
            font-family: "Bookman Old Style", serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
            margin: 0pt;
        }

        .s2 {
            color: black;
            font-family: "Bookman Old Style", serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .s3 {
            color: black;
            font-family: "Bookman Old Style", serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
        }

        .s4 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .s5 {
            color: black;
            font-family: Arial, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
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
            font-family: "Bookman Old Style", serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        /* ... (Tambahkan semua style CSS lainnya dari file asli Anda di sini) ... */
        table,
        tbody {
            vertical-align: top;
            overflow: visible;
        }
    </style>
</head>

<body>
    @foreach ($idSobatUnik as $id_sobat)
        @php
            // Mengambil semua alokasi untuk mitra ini pada periode ini
            $alokasiMitra = $alokasiHonor->where('mitra.id_sobat', $id_sobat);
            $firstAlokasi = $alokasiMitra->first(); // Ambil satu data sebagai referensi

            // Menentukan Jabatan untuk judul surat
            $jabatanUnik = $alokasiMitra->pluck('honor.jabatan')->unique();
            $jabatanSurat = '';
            $istilahJabatan = [
                'PML' => 'petugas pemeriksa lapangan',
                'PPL' => 'petugas pendataan lapangan',
                'PETUGAS ENTRI' => 'petugas pengolahan',
            ];
            if ($jabatanUnik->count() == 1) {
                $jabatanSurat = $istilahJabatan[$jabatanUnik->first()] ?? $jabatanUnik->first();
            } elseif ($jabatanUnik->count() > 1) {
                $jabatanSurat = 'petugas lapangan';
            }

            // Menghitung Total Honor untuk lampiran
            $totalHonor = $alokasiMitra->sum('total_honor');

            // Tanggal Penandatanganan
            $tanggalPenandaTanganan = $c::parse($firstAlokasi->tanggal_mulai_perjanjian);
        @endphp

        <h1 style="padding-top: 3pt; text-indent: 0pt; text-align: center;">
            PERJANJIAN KERJA
        </h1>
        <h1 style="padding-top: 5pt; text-indent: 0pt; text-align: center;">
            {{ strtoupper($jabatanSurat) }} PADA BADAN PUSAT STATISTIK<br>KABUPATEN MEMPAWAH
        </h1>
        <h1 style="padding-left: 20pt; text-indent: 0pt; text-align: center;">
            {{-- PERUBAHAN: Mengambil nomor dari relasi 'kontrak' --}}
            NOMOR: {{ $firstAlokasi->kontrak->nomor_surat_perjanjian_kerja }}
        </h1>
        <p style="padding-top: 11pt; padding-left: 5pt; text-indent: 0pt; text-align: justify;">
            Pada hari ini, {{ $tanggalPenandaTanganan->dayName }},
            tanggal {{ ucwords($bil::make($tanggalPenandaTanganan->day)) }},
            bulan {{ $tanggalPenandaTanganan->monthName }},
            tahun {{ ucwords($bil::make($tanggalPenandaTanganan->year)) }}
            ({{ $tanggalPenandaTanganan->format('d-m-Y') }}), yang bertanda tangan di bawah ini:
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <ol id="l1">
            <li data-list-text="1. ">
                <div style="display: flex;">
                    <div style="text-align: justify; min-width:300px">
                        {{ $ppk->nama }} :
                    </div>
                    <div style="text-align: justify">
                        Pejabat Pembuat Komitmen Badan Pusat
                        Statistik Kabupaten Mempawah, berkedudukan di Jalan Raden Kusno,
                        Kecamatan Mempawah Hilir, Kabupaten Mempawah, bertindak untuk dan atas
                        nama Badan Pusat Statistik Kabupaten Mempawah, selanjutnya disebut
                        sebagai <b>PIHAK PERTAMA</b>.
                    </div>
                </div>
                <p style="text-indent: 0pt; text-align: left"><br /></p>
            </li>
            <li data-list-text="2.">
                <div style="display: flex;">
                    <div style="text-align: justify; min-width:300px">
                        {{-- PERUBAHAN: Mengambil nama dari relasi 'mitra' --}}
                        {{ $firstAlokasi->mitra->nama_1 }} :
                    </div>
                    <div style="text-align: justify">
                        {{ ucwords($jabatanSurat) }},
                        berkedudukan di Kecamatan
                        {{-- PERUBAHAN: Mengambil alamat dari relasi 'mitra' --}}
                        {{ ucwords(strtolower($firstAlokasi->mitra->kecamatan_domisili)) }},
                        Desa/Kelurahan {{ ucwords(strtolower($firstAlokasi->mitra->desa_domisili)) }},
                        bertindak untuk dan atas
                        nama diri sendiri, selanjutnya disebut sebagai <b>PIHAK KEDUA</b>.
                    </div>
                </div>
            </li>
        </ol>

        <p style="padding-top: 11pt; padding-left: 5pt; text-indent: 0pt; text-align: justify;">
            bahwa <b>PIHAK PERTAMA </b>dan <b>PIHAK KEDUA </b>yang secara bersama-sama
            disebut <b>PARA PIHAK</b>, sepakat untuk mengikatkan diri dalam Perjanjian
            Kerja {{ ucwords($jabatanSurat) }} untuk kegiatan yang tercantum pada
            lampiran dokumen perjanjian kerja ini Tahun {{ $c::parse($firstAlokasi->tanggal_akhir_perjanjian)->year }}
            pada
            Badan Pusat
            Statistik Kabupaten Mempawah yang selanjutnya disebut Perjanjian, dengan
            ketentuan-ketentuan sebagai berikut:
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
            Pasal 1
        </h1>
        <h1 style="padding-left: 5pt; text-indent: 0pt; text-align: justify">
            PIHAK PERTAMA <span class="p">memberikan pekerjaan kepada </span>PIHAK
            KEDUA <span class="p">dan </span>PIHAK KEDUA
            <span class="p">menerima pekerjaan dari </span>PIHAK PERTAMA
            <span class="p">sebagai {{ ucwords($jabatanSurat) }} pada Badan Pusat Statistik Kabupaten
                Mempawah, dengan lingkup pekerjaan yang ditetapkan oleh </span>PIHAK PERTAMA<span
                class="p">.</span>
        </h1>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
            Pasal 2
        </h1>
        <p style="padding-left: 5pt; text-indent: 0pt; text-align: justify">
            Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada tugas dan
            tanggung jawab sebagaimana tertuang dalam Buku Penjelasan Umum Survei dan
            ketentuan-ketentuan yang ditetapkan oleh <b>PIHAK PERTAMA</b>.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
            Pasal 3
        </h1>
        <p style="text-indent: 0pt; text-align: center">
            {{-- PERUBAHAN: Mengambil tanggal dari kolom di tabel alokasi_honors --}}
            Jangka Waktu Perjanjian terhitung sejak tanggal
            {{ $c::parse($firstAlokasi->tanggal_mulai_perjanjian)->day }}
            {{ $c::parse($firstAlokasi->tanggal_mulai_perjanjian)->monthName }}
            {{ $c::parse($firstAlokasi->tanggal_mulai_perjanjian)->year }}
            sampai
            dengan tanggal
            {{ $c::parse($firstAlokasi->tanggal_akhir_perjanjian)->day }}
            {{ $c::parse($firstAlokasi->tanggal_akhir_perjanjian)->monthName }}
            {{ $c::parse($firstAlokasi->tanggal_akhir_perjanjian)->year }}.
        </p>
        <br />

        {{-- ... (Sisa Pasal 4 hingga Pasal 13 tetap sama, karena tidak mengandung data dinamis yang berubah) ... --}}

        <p style="padding-left: 5pt; text-indent: 0pt; text-align: justify;">
            Demikian Perjanjian ini dibuat dan ditandatangani oleh
            <b>PARA PIHAK </b>dalam 2 (dua) rangkap asli bermeterai cukup, tanpa
            paksaan dari <b>PIHAK </b>manapun dan untuk dilaksanakan oleh
            <b>PARA PIHAK</b>.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table style="border-collapse: collapse; margin-left: 74.7pt" cellspacing="0">
            <tr style="height: 79pt">
                <td style="width: 160pt">
                    <p class="s2"
                        style="padding-left: 2pt; text-indent: 0pt; line-height: 14pt; text-align: center;">
                        PIHAK KEDUA<span class="s3">,</span>
                    </p>
                    <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                    <p class="s3"
                        style="padding-left: 0pt; text-indent: 0pt; line-height: 13pt; text-align: center;">
                        {{-- PERUBAHAN: Mengambil nama dari relasi 'mitra' --}}
                        {{ $firstAlokasi->mitra->nama_1 }}
                    </p>
                </td>
                <td style="width: 185pt">
                    <p class="s2"
                        style="padding-left: 67pt; text-indent: 0pt; line-height: 14pt; text-align: center;">
                        PIHAK PERTAMA<span class="s3">,</span>
                    </p>
                    <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                    <p class="s3"
                        style="padding-left: 63pt; text-indent: 0pt; line-height: 13pt; text-align: center;">
                        {{ $ppk->nama }}
                    </p>
                </td>
            </tr>
        </table>
        <div class="pagebreak"></div>

        <p class="s4" style="padding-top: 4pt; padding-left: 5pt; text-indent: 0pt; text-align: left;">
            {{-- PERUBAHAN: Mengambil nomor dari relasi 'kontrak' --}}
            Lampiran 1. SPK Nomor {{ $firstAlokasi->kontrak->nomor_surat_perjanjian_kerja }}
        </p>
        <br><br>
        <h1 style="text-indent: 0pt; text-align: center">
            Daftar Kegiatan
        </h1>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table style="border-collapse: collapse; margin-left: 6.17pt" cellspacing="0">
            {{-- Header Tabel (tetap sama) --}}
            <tr style="height: 20pt;">
                <td style="border: 1pt solid black;">
                    <p class="s5" style="padding: 5pt; text-align:center">No</p>
                </td>
                <td style="width: 114pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: center">Nama Kegiatan</p>
                </td>
                <td style="width: 114pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: center">Jabatan</p>
                </td>
                <td style="width: 113pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: center">Jenis Honor</p>
                </td>
                <td style="width: 113pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: center">Beban Kerja</p>
                </td>
                <td style="width: 113pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: left">Honor per Satuan</p>
                </td>
                <td style="width: 113pt; padding: 5pt; border: 1pt solid black;">
                    <p class="s5" style="text-align: left">Honor Maksimal</p>
                </td>
            </tr>

            {{-- LOGIKA BARU: Loop langsung ke koleksi alokasi mitra yang sudah difilter --}}
            @foreach ($alokasiMitra as $alokasi)
                <tr style="height: 20pt">
                    <td style="padding: 10pt; width: 10px; border: 1pt solid black;">
                        <p style="text-indent: 0pt; text-align: left">{{ $loop->iteration }}</p>
                    </td>
                    <td style="width: 300pt; border: 1pt solid black;">
                        {{-- PERUBAHAN: Mengambil data dari relasi honor -> kegiatanManmit --}}
                        <p style="padding: 10pt; text-align: left">{{ $alokasi->honor->kegiatanManmit->nama }}</p>
                    </td>
                    <td style="width: 100pt; border: 1pt solid black;">
                        {{-- PERUBAHAN: Mengambil data dari relasi honor --}}
                        <p style="padding: 10pt; text-align: center">
                            {{ ucwords($istilahJabatan[$alokasi->honor->jabatan] ?? $alokasi->honor->jabatan) }}
                        </p>
                    </td>
                    <td style="width: 100pt; border: 1pt solid black;">
                        <p style="padding: 10pt; text-align: center">
                            {{ ucwords(strtolower($alokasi->honor->jenis_honor)) }}</p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        {{-- PERUBAHAN: Mengambil target dari alokasi dan satuan dari honor --}}
                        <p style="text-indent: 0pt; text-align: center">
                            {{ $alokasi->target_per_satuan_honor . ' ' . $alokasi->honor->satuan_honor }}
                        </p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        {{-- PERUBAHAN: Mengambil harga satuan dari relasi honor --}}
                        <p style="text-indent: 0pt; text-align: center">
                            Rp.{{ $number::format($alokasi->honor->harga_per_satuan) }}</p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        {{-- PERUBAHAN: Mengambil total honor dari kolom alokasi --}}
                        <p style="text-indent: 0pt; text-align: left">Rp.{{ $number::format($alokasi->total_honor) }}
                        </p>
                    </td>
                </tr>
            @endforeach

            {{-- Baris Total --}}
            <tr style="height: 20pt">
                <td colspan="6" style="padding: 10pt; border: 1pt solid black;">
                    <p style="text-indent: 0pt; text-align: center">Total</p>
                </td>
                <td colspan="1" style="padding: 10pt; border: 1pt solid black;">
                    {{-- PERUBAHAN: Menggunakan variabel total yang sudah dihitung di awal --}}
                    <p style="text-indent: 0pt; text-align: left">Rp.{{ $number::format($totalHonor) }}</p>
                </td>
            </tr>
            <tr style="height: 20pt">
                <td colspan="7" style="padding: 10pt; border: 1pt solid black;">
                    <p style="text-indent: 0pt; text-align: center">Terbilang: {{ ucwords($bil::make($totalHonor)) }}
                        rupiah
                    </p>
                </td>
            </tr>
        </table>
        <div class="pagebreak"></div>
    @endforeach
</body>

</html>
