<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ms" lang="ms">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PERJANJIAN KERJA</title>
    <meta name="author" content="Direktorat Statistik Kesejahteraan Rakyat" />
    @php
        // Persiapan variabel helper
        $c = now()->settings(['formatFunction' => 'translatedFormat']);
        $cons = App\Supports\Constants::class;
        $number = \Illuminate\Support\Number::class;
        $bil = \Terbilang::class;

        // LOGIKA BARU: Controller sudah memberikan data yang difilter. Kita tinggal mengelompokkannya per mitra.
        $alokasiPerMitra = $alokasiHonor->groupBy('mitra_id');
    @endphp
    <style type="text/css">
        @page {
            margin: 2cm 2cm 2cm 2cm;
            size: a4 portrait;
        }

        /* Template khusus untuk Lampiran (Landscape) */
        @page landscape-page {
            margin: 1.5cm;
            size: a4 landscape;
        }

        .section-attachment {
            page: landscape-page;
            page-break-before: always;
        }

        .section-contract {
            page-break-before: always;
        }

        .lanskap {
            page: landscape-page;
            page-break-before: always;
        }

        .potret {
            page: portrait;
            page-break-before: always;
        }

        .page-break {
            page-break-after: always;
        }

        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
            font-family: "Bookman Old Style", serif;
            /* Set default font for all elements */
        }

        h1 {
            color: black;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .p,
        p {
            color: black;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
            margin: 0pt;
        }

        .s2 {
            color: black;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .s3 {
            color: black;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
        }

        .s4 {
            color: black;
            /* font-family: Arial, sans-serif;  <- REPLACED */
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        .s5 {
            color: black;
            /* font-family: Arial, sans-serif; <- REPLACED */
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
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        /* Style for lists with numbered clauses (ayat) */
        .pasal-ayat {
            padding-left: 0pt;
            list-style: none;
            counter-reset: ayatCounter 1;
        }

        .pasal-ayat>li {
            text-align: justify;
            padding-left: 34pt;
            text-indent: -29pt;
        }

        .pasal-ayat>li:before {
            counter-increment: ayatCounter;
            content: "(" counter(ayatCounter, decimal) ") ";
            font-size: 12pt;
            font-weight: normal;
        }

        /* Reset counter for the first item to start at 1 */
        .pasal-ayat>li:first-child:before {
            counter-increment: ayatCounter 0;
        }

        /* Style for articles (pasal) with only a single clause (no number needed) */
        .pasal-single-ayat {
            padding-left: 34pt;
            text-align: justify;
        }

        #l7 {
            padding-left: 0pt;
            list-style-type: none;
            counter-reset: h2 1;
        }

        #l7>li {
            padding-left: 20pt;
            position: relative;
        }

        #l7>li:before {
            content: counter(h2, lower-latin) ".";
            counter-increment: h2;
            position: absolute;
            left: 0;
        }

        #l7>li:first-child:before {
            counter-increment: h2 0;
        }

        table,
        tbody {
            vertical-align: top;
            overflow: visible;
        }

        .template-kontrak-custom h1 {
            text-align: center;
            margin-top: 15pt;
            margin-bottom: 5pt;
        }

        .template-kontrak-custom p {
            text-align: justify;
            margin-bottom: 10pt;
            text-indent: 34pt;
        }

        .template-kontrak-custom ol, .template-kontrak-custom ul {
            padding-left: 34pt;
            margin-bottom: 10pt;
        }

        .template-kontrak-custom li {
            text-align: justify;
            margin-bottom: 5pt;
        }
    </style>
</head>

<body>
    @foreach ($alokasiPerMitra as $key => $alokasiMitra)
        @php
            $firstAlokasi = $alokasiMitra->first();
            $mitra = $firstAlokasi->mitra;
            $kontrak = $firstAlokasi->kontrak;
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
            $tanggalPenandaTanganan = $c::parse($firstAlokasi->tanggal_penanda_tanganan_spk_oleh_petugas);
            $totalHonor = $alokasiMitra->sum('total_honor');

            // Hitung tanggal agregat untuk placeholder
            $minStart = $alokasiMitra->min('tanggal_mulai_perjanjian');
            $maxEnd = $alokasiMitra->max('tanggal_akhir_perjanjian');
            $minStartCarbon = $c::parse($minStart);
            $maxEndCarbon = $c::parse($maxEnd);

            // Logika Replacement Placeholder
            $renderedTemplate = '';
            if ($kegiatan?->template_kontrak) {
                $replacements = [
                    $cons::PLACEHOLDER_NAMA_MITRA => $mitra->nama_1,
                    $cons::PLACEHOLDER_NIK_MITRA => $mitra->nik,
                    $cons::PLACEHOLDER_ALAMAT_MITRA => $mitra->alamat_detail,
                    $cons::PLACEHOLDER_NAMA_PPK => $ppk->nama,
                    $cons::PLACEHOLDER_NIP_PPK => $ppk->nip,
                    $cons::PLACEHOLDER_NAMA_KEGIATAN => $kegiatan->nama,
                    $cons::PLACEHOLDER_TOTAL_HONOR => 'Rp. ' . $number::format($totalHonor),
                    $cons::PLACEHOLDER_TANGGAL_MULAI => $minStartCarbon->translatedFormat('d F Y'),
                    $cons::PLACEHOLDER_TANGGAL_SELESAI => $maxEndCarbon->translatedFormat('d F Y'),
                    $cons::PLACEHOLDER_NOMOR_SURAT => $kontrak->nomor_surat_perjanjian_kerja,
                ];
                $renderedTemplate = str_replace(array_keys($replacements), array_values($replacements), $kegiatan->template_kontrak);
            }
        @endphp
        <div class="section-contract">
            <script type="text/php">
                if(!isset($GLOBALS['section_starts'])) { $GLOBALS['section_starts'] = []; }
                $GLOBALS['section_starts'][$pdf->get_page_number()] = 'contract';
            </script>
            <h1 style="padding-top: 3pt; text-align: center;">PERJANJIAN KERJA</h1>
        <h1 style="padding-top: 5pt; text-align: center;">{{ strtoupper($jabatanSurat) }} PADA BADAN PUSAT
            STATISTIK<br>KABUPATEN MEMPAWAH</h1>
        <h1 style="text-align: center;">NOMOR: {{ $kontrak->nomor_surat_perjanjian_kerja }}</h1>
        <p style="padding-top: 11pt; padding-left: 5pt; text-align: justify;">
            Pada hari ini, {{ $tanggalPenandaTanganan->dayName }},
            tanggal {{ ucwords($bil::make($tanggalPenandaTanganan->day)) }},
            bulan {{ $tanggalPenandaTanganan->monthName }},
            tahun {{ ucwords($bil::make($tanggalPenandaTanganan->year)) }}
            ({{ $tanggalPenandaTanganan->format('d-m-Y') }}), yang bertanda tangan di bawah ini:
        </p>
        <p style="text-indent: 0pt; text-align: left;"><br /></p>
        <ol id="l1">
            <li data-list-text="1. ">
                <div style="display: flex;">
                    <div style="text-align: justify; min-width:300px">  {{ $ppk->nama }} :</div>
                    <div style="text-align: justify">Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Mempawah,
                        berkedudukan di Jalan Raden Kusno, Kecamatan Mempawah Hilir, Kabupaten Mempawah, bertindak untuk
                        dan atas nama Badan Pusat Statistik Kabupaten Mempawah, selanjutnya disebut sebagai <b>PIHAK
                            PERTAMA</b>.</div>
                </div>
                <p style="text-indent: 0pt; text-align: left;"><br /></p>
            </li>
            <li data-list-text="2.">
                <div style="display: flex;">
                    <div style="text-align: justify; min-width:300px">  {{ $mitra->nama_1 }} :</div>
                    <div style="text-align: justify">
                        {{ ucwords($jabatanSurat) }}, berkedudukan di Kecamatan
                        {{ ucwords(strtolower($mitra->kecamatanName)) }},
                        Desa/Kelurahan {{ ucwords(strtolower($mitra->desaName)) }},
                        bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai <b>PIHAK KEDUA</b>.
                    </div>
                </div>
            </li>
        </ol>

        <p style="padding-top: 11pt; padding-left: 5pt; text-align: justify;">
            bahwa <b>PIHAK PERTAMA </b>dan <b>PIHAK KEDUA </b>yang secara bersama-sama disebut <b>PARA PIHAK</b>,
            sepakat untuk mengikatkan diri dalam Perjanjian Kerja {{ ucwords($jabatanSurat) }} untuk kegiatan yang
            tercantum pada lampiran dokumen perjanjian kerja ini Tahun
            {{ $c::parse($firstAlokasi->tanggal_akhir_perjanjian)->year }} pada Badan Pusat Statistik Kabupaten
            Mempawah yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:
        </p>

        <p style="text-indent: 0pt; text-align: left;"><br /></p>

        @if($kegiatan?->template_kontrak)
            <div class="template-kontrak-custom">
                {!! $renderedTemplate !!}
            </div>
        @else
            <h1 style="text-indent: 0pt; text-align: center;">Pasal 1</h1>
            <p class="pasal-single-ayat"><span class="s2">PIHAK PERTAMA</span> memberikan pekerjaan kepada <span
                    class="s2">PIHAK KEDUA</span> dan <span class="s2">PIHAK KEDUA</span> menerima pekerjaan dari
                <span class="s2">PIHAK PERTAMA</span> sebagai {{ ucwords($jabatanSurat) }} pada Badan Pusat Statistik
                Kabupaten Mempawah, dengan lingkup pekerjaan yang ditetapkan oleh <span class="s2">PIHAK PERTAMA</span>.
            </p>

            <p style="text-indent: 0pt; text-align: left;"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center;">Pasal 2</h1>
            <p class="pasal-single-ayat">Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada tugas dan tanggung jawab
                sebagaimana tertuang dalam Buku Penjelasan Umum Survei dan ketentuan-ketentuan yang ditetapkan oleh <b>PIHAK
                    PERTAMA</b>.</p>

            <p style="text-indent: 0pt; text-align: left;"><br /></p>
            <p style="text-indent: 0pt; text-align: left;"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center;">Pasal 3</h1>
            <p class="pasal-single-ayat">Jangka Waktu Perjanjian terhitung sejak tanggal
                {{ $minStartCarbon->day }}
                {{ $minStartCarbon->monthName }}
                {{ $minStartCarbon->year }} sampai dengan tanggal
                {{ $maxEndCarbon->day }}
                {{ $maxEndCarbon->monthName }}
                {{ $maxEndCarbon->year }}.</p>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 4</h1>
            <p class="pasal-single-ayat"><span class="s2">PIHAK KEDUA</span> berkewajiban melaksanakan seluruh pekerjaan
                yang diberikan oleh <span class="s2">PIHAK PERTAMA</span> sampai selesai, sesuai ruang lingkup pekerjaan
                sebagaimana dimaksud dalam Pasal 2 dan mematuhi ketentuan- ketentuan yang ditetapkan oleh <span
                    class="s2">PIHAK PERTAMA</span>.</p>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 5</h1>
            <ol class="pasal-ayat">
                <li><span class="s2">PIHAK KEDUA</span> berhak untuk mendapatkan honorarium petugas dari <span
                        class="s2">PIHAK PERTAMA</span> sebesar paling banyak sesuai yang tercantum pada lampiran
                    dokumen ini untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, sudah termasuk biaya pajak, bea meterai,
                    pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</li>
                <li>Pembayaran honorarium sebagaimana dimaksud pada ayat (1) berdasarkan beban kerja <b>PIHAK KEDUA</b>.
                </li>
                <li>Dalam hal <b>PIHAK KEDUA</b> tidak dapat melaksanakan beban kerja sebagaimana dimaksud pada ayat (2),
                    maka pembayaran honorarium akan dihitung secara proporsional sesuai beban kerja yang telah diselesaikan.
                </li>
                <li><span class="s2">PIHAK KEDUA</span> tidak diberikan honorarium tambahan apabila melakukan kunjungan
                    di luar jadwal atau terdapat tambahan waktu penyelesaian pelaksanaan pekerjaan lapangan.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 6</h1>
            <ol class="pasal-ayat">
                <li>Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 ayat (1) dilakukan setelah <b>PIHAK KEDUA
                    </b>menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada
                    <b>PIHAK PERTAMA</b>.
                </li>
                <li>Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh <b>PIHAK PERTAMA </b>kepada <b>PIHAK KEDUA
                    </b>sesuai dengan ketentuan peraturan perundang-undangan.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 7</h1>
            <ol class="pasal-ayat">
                <li>Penyerahan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 dilaksanakan secara bertahap dan
                    berjenjang oleh <b>PIHAK KEDUA </b>kepada <b>PIHAK PERTAMA </b>yang dinyatakan dalam Berita Acara Serah
                    Terima Hasil Pekerjaan dan ditandatangani oleh <b>PARA PIHAK</b>, paling lambat pada tanggal
                    {{ $maxEndCarbon->copy()->addDays(5)->day }}
                    {{ $maxEndCarbon->copy()->addDays(5)->monthName }}
                    {{ $maxEndCarbon->copy()->addDays(5)->year }}.</li>
                <li>Apabila terdapat hambatan dalam penyerahan hasil pekerjaan sebagaimana dimaksud pada ayat (1), <b>PIHAK
                        PERTAMA </b>dapat memberikan tambahan waktu penyerahan seluruh hasil pekerjaan lapangan paling
                    lambat pada tanggal {{ $maxEndCarbon->copy()->addDays(11)->day }}
                    {{ $maxEndCarbon->copy()->addDays(11)->monthName }}
                    {{ $maxEndCarbon->copy()->addDays(11)->year }}.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 8</h1>
            <ol class="pasal-ayat">
                <li><span class="s2">PIHAK PERTAMA</span> dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu
                    dalam hal <span class="s2">PIHAK KEDUA</span> tidak dapat melaksanakan kewajibannya sebagaimana
                    dimaksud dalam Pasal 4, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</li>
                <li>Dalam hal <b>PIHAK PERTAMA </b>memutuskan Perjanjian sebagaimana dimaksud pada ayat (1), maka <b>PIHAK
                        KEDUA </b>tidak menerima dan tidak dapat menuntut pembayaran honorarium dalam bentuk apapun atas
                    pekerjaan yang sudah selesai dilaksanakan oleh <b>PIHAK KEDUA</b>.</li>
                <li>Apabila <b>PIHAK KEDUA </b>diberhentikan sebagaimana dimaksud pada ayat (1), maka <b>PIHAK KEDUA
                    </b>wajib mengembalikan biaya pelatihan yang telah dikeluarkan oleh <b>PIHAK PERTAMA</b>.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 9</h1>
            <ol class="pasal-ayat">
                <li><span class="s2">PIHAK PERTAMA</span> membayarkan honorarium dengan menerbitkan Surat Pemutusan
                    Perjanjian Kerja kepada <span class="s2">PIHAK KEDUA</span> secara proporsional sesuai pekerjaan
                    yang telah dilaksanakan dalam hal <span class="s2">PIHAK KEDUA</span> tidak dapat melaksanakan
                    kewajibannya karena:
                    <ol id="l7">
                        <li>meninggal dunia;</li>
                        <li>sakit dengan keterangan rawat inap;</li>
                        <li>kecelakaan dengan keterangan kepolisian; and/atau</li>
                        <li>ketentuan lain yang ditetapkan oleh <b>PIHAK PERTAMA</b>.</li>
                    </ol>
                </li>
                <li>Pembayaran honorarium sebagaimana dimaksud pada ayat (1) dibayarkan berdasarkan alokasi beban tugas
                    petugas yang ditetapkan oleh <b>PIHAK PERTAMA</b>.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 10</h1>
            <ol class="pasal-ayat">
                <li><span class="s2">PARA PIHAK</span> untuk waktu yang tidak terbatas dan/atau tidak terikat kepada
                    masa berlakunya Perjanjian ini, menjamin kerahasiaan, penggunaan, dan pengamanan data/informasi yang
                    diterima/diperoleh, serta menjamin bahwa data/informasi tersebut hanya dipergunakan untuk melaksanakan
                    tujuan menurut Perjanjian ini.</li>
                <li><span class="s2">PARA PIHAK</span> tidak diperkenankan memberikan dan mengungkapkan data/informasi
                    sebagaimana dimaksud pada ayat (1) dalam bentuk apapun kepada pihak lain.</li>
                <li>Apabila <b>PARA PIHAK </b>melanggar ketentuan sebagaimana dimaksud pada ayat (1) dan ayat (2), akan
                    diberhentikan dan diberikan sanksi sesuai ketentuan peraturan perundang-undangan yang berlaku.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center">Pasal 11</h1>
            <ol class="pasal-ayat">
                <li>Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, <b>PIHAK KEDUA
                    </b>memberitahukan kepada <b>PIHAK PERTAMA </b>dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui
                    atas kejadian Keadaan Kahar dengan menyertakan bukti.</li>
                <li>Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh <b>PIHAK KEDUA </b>dihentikan sementara dan
                    dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak
                    memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan, <b>PIHAK KEDUA </b>berhak menerima
                    honorarium secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
            </ol>

            <p style="text-indent: 0pt; text-align: left;"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center;">Pasal 12</h1>
            <p class="pasal-single-ayat">Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan
                dalam perjanjian tambahan<i>/</i>adendum dan merupakan bagian tidak terpisahkan dari Perjanjian ini.</p>

            <p style="text-indent: 0pt; text-align: left;"><br /></p>
            <h1 style="text-indent: 0pt; text-align: center;">Pasal 13</h1>
            <ol class="pasal-ayat">
                <li>Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan
                    diselesaikan secara musyawarah untuk mufakat.</li>
                <li>Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), <b>PARA PIHAK
                    </b>sepakat menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan
                    Negeri Kabupaten Mempawah.</li>
            </ol>
        @endif

        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="padding-left: 5pt; text-indent: 0pt; text-align: justify;">Demikian Perjanjian ini dibuat dan
            ditandatangani oleh <b>PARA PIHAK </b>dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari
            <b>PIHAK </b>manapun dan untuk dilaksanakan oleh <b>PARA PIHAK</b>.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>

        <table style="border-collapse: collapse; margin-left: 74.7pt" cellspacing="0">
            <tr style="height: 79pt">
                <td style="width: 160pt">
                    <p class="s2"
                        style="padding-left: 2pt; text-indent: 0pt; line-height: 14pt; text-align: center;">PIHAK
                        KEDUA<span class="s3">,</span></p>
                    <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                    <p class="s3"
                        style="padding-left: 0pt; text-indent: 0pt; line-height: 13pt; text-align: center;">
                        {{ $mitra->nama_1 }}</p>
                </td>
                <td style="width: 185pt">
                    <p class="s2"
                        style="padding-left: 67pt; text-indent: 0pt; line-height: 14pt; text-align: center;">PIHAK
                        PERTAMA<span class="s3">,</span></p>
                    <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                    <p class="s3"
                        style="padding-left: 63pt; text-indent: 0pt; line-height: 13pt; text-align: center;">
                        {{ $ppk->nama }}</p>
                </td>
            </tr>
        </table>
        </div> {{-- End section-contract --}}

        <div class="section-attachment">
            <script type="text/php">
                $GLOBALS['section_starts'][$pdf->get_page_number()] = 'attachment';
            </script>

        <p class="s4" style="padding-top: 4pt; padding-left: 5pt; text-indent: 0pt; text-align: left;">Lampiran 1.
            SPK Nomor {{ $kontrak->nomor_surat_perjanjian_kerja }}</p>
        <br><br>
        <h1 style="text-indent: 0pt; text-align: center;">Daftar Kegiatan</h1>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table style="border-collapse: collapse; margin-left: 6.17pt" cellspacing="0">
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

            @foreach ($alokasiMitra as $alokasi)
                <tr style="height: 20pt">
                    <td style="padding: 10pt; width: 10px; border: 1pt solid black;">
                        <p style="text-align: left;">{{ $loop->iteration }}</p>
                    </td>
                    <td style="width: 300pt; border: 1pt solid black;">
                        <p style="padding: 10pt; text-align: left;">{{ $alokasi->honor->kegiatanManmit->nama }}</p>
                    </td>
                    <td style="width: 100pt; border: 1pt solid black;">
                        <p style="padding: 10pt; text-align: center;">
                            {{ ucwords($istilahJabatan[$alokasi->honor->jabatan] ?? $alokasi->honor->jabatan) }}</p>
                    </td>
                    <td style="width: 100pt; border: 1pt solid black;">
                        <p style="padding: 10pt; text-align: center;">
                            {{ ucwords(strtolower($alokasi->honor->jenis_honor)) }}</p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        <p style="text-align: center;">
                            {{ (int) $alokasi->target_per_satuan_honor . ' ' . $alokasi->honor->satuan_honor }}</p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        <p style="text-align: center;">Rp.{{ $number::format($alokasi->honor->harga_per_satuan) }}</p>
                    </td>
                    <td style="padding: 10pt; border: 1pt solid black;">
                        <p style="text-align: left;">Rp.{{ $number::format($alokasi->total_honor) }}</p>
                    </td>
                </tr>
            @endforeach
            <tr style="height: 20pt">
                <td colspan="6" style="padding: 10pt; border: 1pt solid black;">
                    <p style="text-align: center;">Total</p>
                </td>
                <td colspan="1" style="padding: 10pt; border: 1pt solid black;">
                    <p style="text-align: left;">Rp.{{ $number::format($totalHonor) }}</p>
                </td>
            </tr>
            <tr style="height: 20pt">
                <td colspan="7" style="padding: 10pt; border: 1pt solid black;">
                    <p style="text-align: center;">Terbilang: {{ $bil::make($totalHonor) }} rupiah</p>
                </td>
            </tr>
        </table>
        </div> {{-- End section-attachment --}}
        <div class="page-break"></div>
    @endforeach

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $starts = $GLOBALS["section_starts"] ?? [];
                $keys = array_keys($starts);
                sort($keys);
                
                // Cari start page untuk halaman ini
                $this_start = 1;
                foreach($keys as $k) {
                    if ($PAGE_NUM >= $k) {
                        $this_start = $k;
                    }
                }
                
                $relative_page = $PAGE_NUM - $this_start + 1;
                
                // Tampilkan hanya jika bukan halaman pertama bagian tersebut
                if ($relative_page > 1) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $w = $pdf->get_width();
                    $h = $pdf->get_height();
                    
                    $text = "- $relative_page -";
                    $size = 10;
                    $width = $fontMetrics->get_text_width($text, $font, $size);
                    
                    // Posisi Tengah Atas
                    $x = ($w - $width) / 2;
                    $y = 25; 
                    
                    $pdf->text($x, $y, $text, $font, $size);
                }
            ');
        }
    </script>
</body>

</html>
