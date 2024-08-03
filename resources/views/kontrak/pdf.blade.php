
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ms" lang="ms">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PERJANJIAN KERJA_template</title>
    <meta name="author" content="Direktorat Statistik Kesejahteraan Rakyat" />
    @php
            $c = now()->setLocale('id_ID');
            $c = now()->settings(['formatFunction'=>'translatedFormat']);
            $cons = App\Supports\Constants::class;
            $number = \Illuminate\Support\Number::class;
            $bil = \Terbilang::class;

            if($id_honor){
                $alokasiHonorBulan = $alokasiHonor->where('tahun_akhir_kegiatan',$tahun)->where('bulan_akhir_kegiatan',$bulan)->where('id_honor',$id_honor);
            }else{
                $alokasiHonorBulan = $alokasiHonor->where('tahun_akhir_kegiatan',$tahun)->where('bulan_akhir_kegiatan',$bulan);
            }
            $idSobat = $alokasiHonorBulan->pluck('id_sobat')->unique()->flatten();

    @endphp
    <style type="text/css">
        @media print {
            .pagebreak { page-break-before: always; } /* page-break-after works, as well */
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
      #l1 > li > *:first-child:before {
        counter-increment: c1;
        content: counter(c1, decimal) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l1 > li:first-child > *:first-child:before {
        counter-increment: c1 0;
      }
      li {
        display: block;
      }
      #l2 {
        padding-left: 0pt;
        counter-reset: d1 1;
      }
      #l2 > li > *:first-child:before {
        counter-increment: d1;
        content: "(" counter(d1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l2 > li:first-child > *:first-child:before {
        counter-increment: d1 0;
      }
      li {
        display: block;
      }
      #l3 {
        padding-left: 0pt;
        counter-reset: e1 1;
      }
      #l3 > li > *:first-child:before {
        counter-increment: e1;
        content: "(" counter(e1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l3 > li:first-child > *:first-child:before {
        counter-increment: e1 0;
      }
      li {
        display: block;
      }
      #l4 {
        padding-left: 0pt;
        counter-reset: f1 1;
      }
      #l4 > li > *:first-child:before {
        counter-increment: f1;
        content: "(" counter(f1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l4 > li:first-child > *:first-child:before {
        counter-increment: f1 0;
      }
      li {
        display: block;
      }
      #l5 {
        padding-left: 0pt;
        counter-reset: g1 1;
      }
      #l5 > li > *:first-child:before {
        counter-increment: g1;
        content: "(" counter(g1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l5 > li:first-child > *:first-child:before {
        counter-increment: g1 0;
      }
      li {
        display: block;
      }
      #l6 {
        padding-left: 0pt;
        counter-reset: h1 1;
      }
      #l6 > li > *:first-child:before {
        counter-increment: h1;
        content: "(" counter(h1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l6 > li:first-child > *:first-child:before {
        counter-increment: h1 0;
      }
      #l7 {
        padding-left: 0pt;
        counter-reset: h2 1;
      }
      #l7 > li > *:first-child:before {
        counter-increment: h2;
        content: counter(h2, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l7 > li:first-child > *:first-child:before {
        counter-increment: h2 0;
      }
      li {
        display: block;
      }
      #l8 {
        padding-left: 0pt;
        counter-reset: i1 1;
      }
      #l8 > li > *:first-child:before {
        counter-increment: i1;
        content: "(" counter(i1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l8 > li:first-child > *:first-child:before {
        counter-increment: i1 0;
      }
      li {
        display: block;
      }
      #l9 {
        padding-left: 0pt;
        counter-reset: j1 1;
      }
      #l9 > li > *:first-child:before {
        counter-increment: j1;
        content: "(" counter(j1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l9 > li:first-child > *:first-child:before {
        counter-increment: j1 0;
      }
      li {
        display: block;
      }
      #l10 {
        padding-left: 0pt;
        counter-reset: k1 1;
      }
      #l10 > li > *:first-child:before {
        counter-increment: k1;
        content: "(" counter(k1, decimal) ") ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 12pt;
      }
      #l10 > li:first-child > *:first-child:before {
        counter-increment: k1 0;
      }
      table,
      tbody {
        vertical-align: top;
        overflow: visible;
      }
    </style>
  </head>
  <body>
    @php
        $page = 0;
    @endphp
    @foreach ($idSobat as $s)
        @php
            $page += 1;
            $alokasiHonorBulanMitra = $alokasiHonorBulan->where('id_sobat',$s);
            $idHonor = $alokasiHonorBulanMitra->pluck('id_honor')->unique()->flatten();
            $jabatan = $alokasiHonorBulanMitra->pluck('jabatan')->unique();
            $jabatanSurat = "";
            $istilahJabatan = [
                    "PML"=>"petugas pemeriksa lapangan",
                    "PPL"=>"petugas pendataan lapangan",
                    "PETUGAS ENTRI"=>"petugas pengolahan"
            ];
            $istilahKodeJabatan = [
                "PML"=>"PML",
                "PPL"=>"PPL",
                "PETUGAS ENTRI"=>"PETUGAS PENGOLAHAN",
            ];
            if(count($jabatan) == 1){
                $jabatan = $jabatan->first();
                if($jabatan == "PETUGAS ENTRI") $jabatanSurat = "petugas pengolahan";
                else $jabatanSurat = $istilahJabatan[$jabatan];
            }elseif(count($jabatan)>1){
                $jabatanSurat = "petugas lapangan";
            }
        @endphp
        <h1
        style="
            padding-top: 3pt;
            padding-left: 20pt;
            text-indent: 0pt;
            text-align: center;
        "
        >
        PERJANJIAN KERJA
        </h1>
        <h1
        style="
            padding-top: 5pt;
            padding-left: 20pt;
            text-indent: 0pt;
            text-align: center;
        "
        >
        {{strtoupper($jabatanSurat)}} PADA BADAN PUSAT STATISTIK<br>KABUPATEN MEMPAWAH
        </h1>
        <h1 style="padding-left: 20pt; text-indent: 0pt; text-align: center">
        NOMOR: {{$alokasiHonorBulanMitra->first()->suratPerjanjianKerja->nomor_surat_perjanjian_kerja}}
        </h1>
        <p
        style="
            padding-top: 11pt;
            padding-left: 5pt;
            text-indent: 0pt;
            text-align: justify;
        "
        >
        @php
            $tanggalPenandaTanganan = $c::parse($alokasiHonorBulanMitra->first()->tanggal_penanda_tanganan_spk_oleh_petugas);
        @endphp
        Pada hari ini, {{$tanggalPenandaTanganan->dayName}},
        tanggal {{$bil::make($tanggalPenandaTanganan->day)}},
        bulan {{$tanggalPenandaTanganan->monthName}},
        tahun {{$bil::make($tanggalPenandaTanganan->year)}}
        ({{$tanggalPenandaTanganan->format('d-m-Y')}}), yang bertanda tangan di bawah ini:
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <ol id="l1">
        <li data-list-text="1. ">
            <div style="display: flex;">
                <div style="text-align: justify; min-width:300px">
                    &nbsp; {{$ppk->nama}} :
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
                &nbsp; {{$alokasiHonorBulanMitra->first()->nama_petugas}} :
                </div>
                <div style="text-align: justify">
                    {{ucwords($jabatanSurat)}},
                    berkedudukan di Kecamatan
                    {{ucwords(strtolower($alokasiHonorBulanMitra->first()->kecamatan_domisili))}},
                    Desa/Kelurahan {{ucwords(strtolower($alokasiHonorBulanMitra->first()->desa_domisili))}},
                    bertindak untuk dan atas
                nama diri sendiri, selanjutnya disebut sebagai <b>PIHAK KEDUA</b>.
                </div>
            </div>
        </li>
        </ol>

        <p
        style="
            padding-top: 11pt;
            padding-left: 5pt;
            text-indent: 0pt;
            text-align: justify;
        "
        >
        bahwa <b>PIHAK PERTAMA </b>dan <b>PIHAK KEDUA </b>yang secara bersama-sama
        disebut <b>PARA PIHAK</b>, sepakat untuk mengikatkan diri dalam Perjanjian
        Kerja {{ucwords($jabatanSurat)}} untuk kegiatan yang tercantum pada
        lampiran dokumen perjanjian kerja ini Tahun {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->year}} pada Badan Pusat
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
        <span class="p"
            >sebagai {{ucwords($jabatanSurat)}} pada Badan Pusat Statistik Kabupaten
            Mempawah, dengan lingkup pekerjaan yang ditetapkan oleh </span
        >PIHAK PERTAMA<span class="p">.</span>
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
        Jangka Waktu Perjanjian terhitung sejak tanggal
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_mulai_perjanjian)->day}}
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_mulai_perjanjian)->monthName}}
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_mulai_perjanjian)->year}}
        sampai
        dengan tanggal
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->day}}
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->monthName}}
        {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->year}}.
        </p>
        <br/>
        <h1 style="text-indent: 0pt; text-align: center">
        Pasal 4
        </h1>
        <h1 style="padding-left: 5pt; text-indent: 0pt; text-align: justify">
        PIHAK KEDUA
        <span class="p"
            >berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh </span
        >PIHAK PERTAMA
        <span class="p"
            >sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud
            dalam Pasal 2 dan mematuhi ketentuan- ketentuan yang ditetapkan oleh </span
        >PIHAK PERTAMA<span class="p">.</span>
        </h1>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
        Pasal 5
        </h1>
        <ol id="l2">
        <li data-list-text="(1)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PIHAK KEDUA
            <span class="p"
                >berhak untuk mendapatkan honorarium petugas dari </span
            >PIHAK PERTAMA
            <span class="p"
                >sebesar paling banyak sesuai yang tercantum pada lampiran dokumen
                ini untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, sudah
                termasuk biaya pajak, bea meterai, pulsa dan kuota internet untuk
                komunikasi, dan jasa pelayanan keuangan.</span
            >
            </h1>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Pembayaran honorarium sebagaimana dimaksud pada ayat (1) berdasarkan
            beban kerja <b>PIHAK KEDUA</b>.
            </p>
        </li>
        <li data-list-text="(3)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Dalam hal <b>PIHAK KEDUA </b>tidak dapat melaksanakan beban kerja
            sebagaimana dimaksud pada ayat (2), maka pembayaran honorarium akan
            dihitung secara proporsional sesuai beban kerja yang telah
            diselesaikan.
            </p>
        </li>
        <li data-list-text="(4)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PIHAK KEDUA
            <span class="p"
                >tidak diberikan honorarium tambahan apabila melakukan kunjungan di
                luar jadwal atau terdapat tambahan waktu penyelesaian pelaksanaan
                pekerjaan lapangan.</span
            >
            </h1>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
        Pasal 6
        </h1>
        <ol id="l3">
        <li data-list-text="(1)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 ayat (1)
            dilakukan setelah <b>PIHAK KEDUA </b>menyelesaikan dan menyerahkan
            seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada
            <b>PIHAK PERTAMA</b>.
            </p>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh
            <b>PIHAK PERTAMA </b>kepada <b>PIHAK KEDUA </b>sesuai dengan ketentuan
            peraturan perundang-undangan.
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 7
        </h1>
        <ol id="l4">
        <li data-list-text="(1)">
            <p
            style="
                padding-left: 34pt;
                text-indent: -28pt;
                line-height: 14pt;
                text-align: justify;
            "
            >
            Penyerahan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal
            </p>
            <p style="padding-left: 34pt; text-indent: 0pt; text-align: justify">
            2 dilaksanakan secara bertahap dan berjenjang oleh
            <b>PIHAK KEDUA </b>kepada <b>PIHAK PERTAMA </b>yang dinyatakan dalam
            Berita Acara Serah Terima Hasil Pekerjaan dan ditandatangani oleh
            <b>PARA PIHAK</b>, paling lambat pada tanggal
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(5)->day}}
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(5)->monthName}}
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(5)->year}}.
            </p>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Apabila terdapat hambatan dalam penyerahan hasil pekerjaan sebagaimana
            dimaksud pada ayat (1), <b>PIHAK PERTAMA </b>dapat memberikan tambahan
            waktu penyerahan seluruh hasil pekerjaan lapangan paling lambat pada
            tanggal
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(11)->day}}
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(11)->monthName}}
            {{$c::parse($alokasiHonorBulanMitra->first()->tanggal_akhir_perjanjian)->addDay(11)->year}}.
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 8
        </h1>
        <ol id="l5">
        <li data-list-text="(1)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PIHAK PERTAMA
            <span class="p"
                >dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam
                hal </span
            >PIHAK KEDUA
            <span class="p"
                >tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam
                Pasal 4, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</span
            >
            </h1>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Dalam hal <b>PIHAK PERTAMA </b>memutuskan Perjanjian sebagaimana
            dimaksud pada ayat (1), maka <b>PIHAK KEDUA </b>tidak menerima dan
            tidak
            </p>
            <p
            style="
                padding-top: 4pt;
                padding-left: 34pt;
                text-indent: 0pt;
                text-align: justify;
            "
            >
            dapat menuntut pembayaran honorarium dalam bentuk apapun atas
            pekerjaan yang sudah selesai dilaksanakan oleh <b>PIHAK KEDUA</b>.
            </p>
        </li>
        <li data-list-text="(3)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Apabila <b>PIHAK KEDUA </b>diberhentikan sebagaimana dimaksud pada
            ayat (1), maka <b>PIHAK KEDUA </b>wajib mengembalikan biaya pelatihan
            yang telah dikeluarkan oleh <b>PIHAK PERTAMA</b>.
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1 style="text-indent: 0pt; text-align: center">
        Pasal 9
        </h1>
        <ol id="l6">
        <li data-list-text="(1)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PIHAK PERTAMA
            <span class="p"
                >membayarkan honorarium dengan menerbitkan Surat Pemutusan
                Perjanjian Kerja kepada </span
            >PIHAK KEDUA
            <span class="p"
                >secara proporsional sesuai pekerjaan yang telah dilaksanakan dalam
                hal </span
            >PIHAK KEDUA
            <span class="p">tidak dapat melaksanakan kewajibannya karena:</span>
            </h1>
            <ol id="l7">
            <li data-list-text="a.">
                <p
                style="
                    padding-left: 54pt;
                    text-indent: -19pt;
                    line-height: 14pt;
                    text-align: left;
                "
                >
                meninggal dunia;
                </p>
            </li>
            <li data-list-text="b.">
                <p
                style="
                    padding-left: 54pt;
                    text-indent: -19pt;
                    line-height: 14pt;
                    text-align: left;
                "
                >
                sakit dengan keterangan rawat inap;
                </p>
            </li>
            <li data-list-text="c.">
                <p
                style="
                    padding-left: 54pt;
                    text-indent: -19pt;
                    line-height: 14pt;
                    text-align: left;
                "
                >
                kecelakaan dengan keterangan kepolisian; dan/atau
                </p>
            </li>
            <li data-list-text="d.">
                <p
                style="
                    padding-left: 54pt;
                    text-indent: -19pt;
                    line-height: 14pt;
                    text-align: left;
                "
                >
                ketentuan lain yang ditetapkan oleh <b>PIHAK PERTAMA</b>.
                </p>
            </li>
            </ol>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Pembayaran honorarium sebagaimana dimaksud pada ayat (1) dibayarkan
            berdasarkan alokasi beban tugas petugas yang ditetapkan oleh
            <b>PIHAK PERTAMA</b>.
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 10
        </h1>
        <ol id="l8">
        <li data-list-text="(1)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PARA PIHAK
            <span class="p"
                >untuk waktu yang tidak terbatas dan/atau tidak terikat kepada masa
                berlakunya Perjanjian ini, menjamin kerahasiaan, penggunaan, dan
                pengamanan data/informasi yang diterima/diperoleh, serta menjamin
                bahwa data/informasi tersebut hanya dipergunakan untuk melaksanakan
                tujuan menurut Perjanjian ini.</span
            >
            </h1>
        </li>
        <li data-list-text="(2)">
            <h1 style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            PARA PIHAK
            <span class="p"
                >tidak diperkenankan memberikan dan mengungkapkan data/informasi
                sebagaimana dimaksud pada ayat (1) dalam bentuk apapun kepada pihak
                lain.</span
            >
            </h1>
        </li>
        <li data-list-text="(3)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Apabila <b>PARA PIHAK </b>melanggar ketentuan sebagaimana dimaksud
            pada ayat (1) dan ayat (2), akan diberhentikan dan diberikan sanksi
            sesuai ketentuan peraturan perundang-undangan yang berlaku.
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 11
        </h1>
        <ol id="l9">
        <li data-list-text="(1)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana
            sosial, <b>PIHAK KEDUA </b>memberitahukan kepada
            <b>PIHAK PERTAMA </b>dalam waktu paling lambat 7 (tujuh) hari sejak
            mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.
            </p>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh
            <b>PIHAK KEDUA </b>dihentikan sementara dan dilanjutkan kembali
            setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar
            tidak memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan,
            <b>PIHAK KEDUA </b>berhak menerima honorarium secara proporsional
            sesuai pekerjaan yang telah dilaksanakan.
            </p>
        </li>
        </ol>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 12
        </h1>
        <p
        style="
            padding-top: 6pt;
            padding-left: 5pt;
            text-indent: 0pt;
            text-align: justify;
        "
        >
        Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini,
        dituangkan dalam perjanjian tambahan<i>/</i>adendum dan merupakan bagian
        tidak terpisahkan dari Perjanjian ini.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <h1
        style="text-indent: 0pt; text-align: center"
        >
        Pasal 13
        </h1>
        <ol id="l10">
        <li data-list-text="(1)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat
            adanya Perjanjian ini akan diselesaikan secara musyawarah untuk
            mufakat.
            </p>
        </li>
        <li data-list-text="(2)">
            <p style="padding-left: 34pt; text-indent: -28pt; text-align: justify">
            Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud
            pada ayat (1), <b>PARA PIHAK </b>sepakat menyelesaikan perselisihan
            dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri
            Kabupaten Mempawah
            </p>
        </li>
        </ol>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="padding-left: 5pt; text-indent: 0pt; text-align: justify">
        Demikian Perjanjian ini dibuat dan ditandatangani oleh
        <b>PARA PIHAK </b>dalam 2 (dua) rangkap asli bermeterai cukup, tanpa
        paksaan dari <b>PIHAK </b>manapun dan untuk dilaksanakan oleh
        <b>PARA PIHAK</b>.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table
        style="border-collapse: collapse; margin-left: 74.7pt"
        cellspacing="0"
        >
        <tr style="height: 79pt">
            <td style="width: 160pt">
            <p
                class="s2"
                style="
                padding-left: 2pt;
                text-indent: 0pt;
                line-height: 14pt;
                text-align: center;
                "
            >
                PIHAK KEDUA<span class="s3">,</span>
            </p>
            <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
            <p
                class="s3"
                style="
                padding-left: 0pt;
                text-indent: 0pt;
                line-height: 13pt;
                text-align: center;
                "
            >
                {{$alokasiHonorBulanMitra->first()->nama_petugas}}
            </p>
            </td>
            <td style="width: 185pt">
            <p
                class="s2"
                style="
                padding-left: 67pt;
                text-indent: 0pt;
                line-height: 14pt;
                text-align: center;
                "
            >
                PIHAK PERTAMA<span class="s3">,</span>
            </p>
            <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
            <p
                class="s3"
                style="
                padding-left: 63pt;
                text-indent: 0pt;
                line-height: 13pt;
                text-align: center;
                "
            >
                {{$ppk->nama}}
            </p>
            </td>
        </tr>
        </table>
        <div class="pagebreak"></div>

        <p
        class="s4"
        style="
            padding-top: 4pt;
            padding-left: 5pt;
            text-indent: 0pt;
            text-align: left;
        "
        >
        Lampiran 1. SPK Nomor {{$alokasiHonorBulanMitra->first()->suratPerjanjianKerja->nomor_surat_perjanjian_kerja}}
        </p>
        <br><br>
        <h1 style="text-indent: 0pt; text-align: center">
            Daftar Kegiatan
        </h1>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table
        style="border-collapse: collapse; margin-left: 6.17pt"
        cellspacing="0"
        >
        <tr style="height: 20pt;">
            <td
            style="
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding: 5pt; text-indent: 0pt; text-align:center"
            >
                No
            </p>
            </td>
            <td
            style="
                width: 114pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: center"
            >
                Nama Kegiatan
            </p>
            </td>
            <td
            style="
                width: 114pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: center"
            >
                Jabatan
            </p>
            </td>
            <td
            style="
                width: 113pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: center"
            >
                Jenis Honor
            </p>
            </td>
            <td
            style="
                width: 113pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: center"
            >
                Beban Kerja
            </p>
            </td>
            <td
            style="
                width: 113pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: left"
            >
                Honor per Satuan
            </p>
            </td>
            <td
            style="
                width: 113pt;
                padding: 5pt;
                border-top-style: solid;
                border-top-width: 1pt;
                border-left-style: solid;
                border-left-width: 1pt;
                border-bottom-style: solid;
                border-bottom-width: 1pt;
                border-right-style: solid;
                border-right-width: 1pt;
            "
            >
            <p
                class="s5"
                style="padding-left: 5pt; text-indent: 0pt; text-align: left"
            >
                Honor Maksimal
            </p>
            </td>
        </tr>
        @php
            $totalHonor = 0;
        @endphp
        @foreach ($idHonor as $k)
            @php
                $alokasiHonorBulanMitraKegiatan = $alokasiHonorBulanMitra->where('id_honor',$k);
                $totalHonor += $alokasiHonorBulanMitraKegiatan->first()->target_honor;
            @endphp
            {{-- <p>
                Nama Kegiatan:  <br>
                Jabatan: {{$alokasiHonorBulanMitraKegiatan->first()->jabatan}} <br>
                Satuan Honor: {{$alokasiHonorBulanMitraKegiatan->first()->satuan_honor}} <br>
                Target per Satuan Honor: {{$alokasiHonorBulanMitraKegiatan->first()->target_per_satuan_honor}} <br>
                Honor per Satuan Honor: Rp.{{$number::format($alokasiHonorBulanMitraKegiatan->first()->honor_per_satuan_honor)}} <br>
                Target Honor: Rp.{{$number::format($alokasiHonorBulanMitraKegiatan->first()->target_honor)}} <br>
                <br>
                <br>
            </p> --}}
            <tr style="height: 20pt">
                <td
                style="
                    padding: 10pt;
                    width: 10px;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: left">{{$loop->index+1}}<br /></p>
                </td>
                <td
                style="
                    width: 300pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="padding: 10pt; text-align: left">{{$alokasiHonorBulanMitraKegiatan->first()->nama_kegiatan}}</p>
                </td>
                <td
                style="
                    width: 100pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="padding: 10pt; text-align: center">{{ucwords($istilahJabatan[$alokasiHonorBulanMitraKegiatan->first()->jabatan])}}</p>
                </td>
                <td
                style="
                    width: 100pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="padding: 10pt; text-align: center">{{ucwords(strtolower($alokasiHonorBulanMitraKegiatan->first()->jenis_honor))}}</p>
                </td>
                <td
                style="
                    padding: 10pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: center">{{$alokasiHonorBulanMitraKegiatan->first()->target_per_satuan_honor." ".$alokasiHonorBulanMitraKegiatan->first()->satuan_honor}}</p>
                </td>
                <td
                style="
                    padding: 10pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: center">Rp.{{$number::format($alokasiHonorBulanMitraKegiatan->first()->honor_per_satuan_honor)}}</p>
                </td>
                <td
                style="
                    padding: 10pt;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: left">Rp.{{$number::format($alokasiHonorBulanMitraKegiatan->first()->target_honor)}}</p>
                </td>
            </tr>
        @endforeach
            <tr style="height: 20pt">
                <td
                colspan="6"
                style="
                    padding: 10pt;
                    width: 10px;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: center">Total</p>
                </td>
                <td
                colspan="1"
                style="
                    padding: 10pt;
                    width: 10px;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: left">Rp.{{$number::format($totalHonor)}}</p>
                </td>
            </tr>
            <tr style="height: 20pt">
                <td
                colspan="7"
                style="
                    padding: 10pt;
                    width: 10px;
                    border-top-style: solid;
                    border-top-width: 1pt;
                    border-left-style: solid;
                    border-left-width: 1pt;
                    border-bottom-style: solid;
                    border-bottom-width: 1pt;
                    border-right-style: solid;
                    border-right-width: 1pt;
                "
                >
                <p style="text-indent: 0pt; text-align: center">Terbilang: {{$bil::make($totalHonor)}} rupiah</p>
                </td>
            </tr>
        </table>
        <div class="pagebreak"></div>
    @endforeach
    </div>
  </body>
</html>


