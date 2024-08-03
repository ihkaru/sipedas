<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ms" lang="ms">
  <head>
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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>BAST_6104</title>
    <meta name="author" content="BPS6111" />
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
      #l1 > li > *:first-child:before {
        counter-increment: c1;
        content: counter(c1, decimal) ". ";
        color: black;
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 11pt;
      }
      #l1 > li:first-child > *:first-child:before {
        counter-increment: c1 0;
      }
      #l2 {
        padding-left: 0pt;
        counter-reset: c2 1;
      }
      #l2 > li > *:first-child:before {
        counter-increment: c2;
        content: counter(c2, lower-latin) ". ";
        color: black;
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 11pt;
      }
      #l2 > li:first-child > *:first-child:before {
        counter-increment: c2 0;
      }
    </style>
  </head>
  <body>
    @foreach ($idSobat as $s)
        @php
            $alokasiHonorBulanMitra = $alokasiHonorBulan->where('id_sobat',$s);
            $idKegiatan = $alokasiHonorBulanMitra->pluck('id_kegiatan')->unique()->flatten();
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
        @foreach ($idKegiatan as $k)
            @php
                $alokasiHonorBulanMitraKegiatan = $alokasiHonorBulanMitra->where('id_kegiatan',$k);
                $tanggalSurat = $c::parse($alokasiHonorBulanMitraKegiatan->first()->suratBast->tanggal_nomor);
                $jenisPekerjaan = "";
                if($alokasiHonorBulanMitraKegiatan->first()->jabatan == "PPL" || $alokasiHonorBulanMitraKegiatan->first()->jabatan == "PML") $jenisPekerjaan = "pendataan";
                if($alokasiHonorBulanMitraKegiatan->first()->jabatan == "PETUGAS ENTRI") $jenisPekerjaan = "pengolahan";
            @endphp
        <div class="pagebreak"></div>
        <div style="display: flex;">
            <div style="max-width: 600px; display: flex">
                <div>
                    <span>
                        <img width=97 height=69 src="{{asset('SPPD_files/Image_001.png')}}" v:shapes="Picture_x0020_1">
                    </span>
                </div>
                <div
                  style="
                    text-indent: 0pt;
                    line-height: 107%;
                    text-align: left;
                    align-self: center;
                    padding-left: 10px;
                  "
                >
                <h1 style="padding-bottom: 10pt;">
                    BADAN PUSAT STATISTIK
                </h1>
                <h1>KABUPATEN MEMPAWAH</h1>
                <p
                    class="s2"
                    style="text-indent: 0pt; text-align: left; padding-top:10pt"
                    >
                    <a href="https://mempawahkab.bps.go.id/" class="s3" target="_blank"
                        >Jln. Raden Kusno No 59 Mempawah 78912 Telp (+62561)691049 Fax (+62561)
                        6695439 Homepage: </a
                    ><span
                        style="
                        color: #00f;
                        font-family: Calibri, sans-serif;
                        font-style: normal;
                        font-weight: normal;
                        text-decoration: underline;
                        font-size: 8pt;
                        "
                        >https://mempawahkab.bps.go.id</span
                    >
                    <a href="mailto:bps6104@bps.go.id" class="s3" target="_blank"
                        >E-mail: bps6104@bps.go.id</a
                    >
                    </p>
                </div>
            </div>
            <div style="width: 200px;"></div>
            <div style="text-align: right;">

            </div>
        </div>

        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p
          class="s4"
          style="
            padding-top: 4pt;
            text-indent: 0pt;
            text-align: center;
          "
        >
          <a name="bookmark0">BERITA ACARA SERAH TERIMA PEKERJAAN</a>
        </p>
        <h3 style="text-indent: 0pt; text-align: center">
          Nomor : {{$alokasiHonorBulanMitraKegiatan->first()->suratBast->nomor_surat_bast}}
        </h3>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p
          style="
            padding-left: 5pt;
            text-indent: 0pt;
            line-height: 150%;
            text-align: left;
          "
        >
          Pada hari ini, {{$tanggalSurat->dayName}}, Tanggal
          {{ucwords($bil::make($tanggalSurat->day))}} Bulan
          {{$tanggalSurat->monthName}} Tahun {{ucwords($bil::make($tanggalSurat->year))}},
          kami yang bertanda tangan di bawah ini:
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table style="padding-left: 50px;">
            <tr>
                <td style="width: 30px;"><p>1.</p></td>
                <td style="width: 100px;"><p>Nama/NIP</p></td>
                <td style="width: 20px;"><p>:</p></td>
                <td><p>{{$ppk->nama}}/ NIP. {{$ppk->nip}}</p></td>
            </tr>
            <tr style="vertical-align: bottom;">
                <td></td>
                <td style="vertical-align: top;"><p>Jabatan</p></td>
                <td style="vertical-align: top;"><p>:</p></td>
                <td><p>Pejabat Pembuat Komitmen BPS Kabupaten Mempawah</p></td>
            </tr>
            <tr style="vertical-align: bottom;">
                <td></td>
                <td style="vertical-align: top;"><p>Alamat</p></td>
                <td style="vertical-align: top;"><p>:</p></td>
                <td><p>Jl. Raden Kusno No. 59, RT. 001 / RW. 001 Kel. Terusan, Kec. Mempawah Hilir, Kab. Mempawah</p></td>
            </tr>
        </table>
        <br>
        <p style="padding-left: 19pt; text-indent: 0pt; text-align: left">
            Dalam hal ini bertindak untuk dan atas nama Badan Pusat Statistik
            Kabupaten Mempawah, selanjutnya disebut <b>PIHAK PERTAMA</b>.
        </p>
        <br>
        <table style="padding-left: 50px;">
            <tr style="vertical-align:bottom;">
                <td style="width: 30px;"><p>2.</p></td>
                <td style="width: 100px;"><p>Nama/NIP</p></td>
                <td style="width: 20px;"><p>:</p></td>
                <td><p>{{$alokasiHonorBulanMitraKegiatan->first()->nama_petugas}}</p></td>
            </tr>
            <tr style="vertical-align: bottom;">
                <td></td>
                <td style="vertical-align: top;"><p>Jabatan</p></td>
                <td style="vertical-align: top;"><p>:</p></td>
                <td><p>{{ucwords($istilahJabatan[$alokasiHonorBulanMitraKegiatan->first()->jabatan])}}</p></td>
            </tr>
            <tr style="vertical-align: bottom;">
                <td></td>
                <td style="vertical-align: top;"><p>Alamat</p></td>
                <td style="vertical-align: top;"><p>:</p></td>
                <td><p>Kecamatan
                    {{ucwords(strtolower($alokasiHonorBulanMitra->first()->kecamatan_domisili))}},
                    Desa/Kelurahan {{ucwords(strtolower($alokasiHonorBulanMitra->first()->desa_domisili))}}</p></td>
            </tr>
        </table>
        <br>
        <p style="padding-left: 18pt; text-indent: 0pt; text-align: left">
            Dalam hal ini bertindak untuk dan atas nama dirinya sendiri,
            selanjutnya disebut <b>PIHAK KEDUA</b>
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p style="padding-left: 5pt; text-indent: 0pt; text-align: left">
            Menyatakan bahwa :
          </p>
          <h3
            style="
            padding-top: 6pt;
            padding-left: 58pt;
            text-indent: -18pt;
            line-height: 150%;
            text-align: left;
            "
        >
            <span class="p">a. </span>
            PIHAK KEDUA
            <span class="p">telah menyelesaikan pekerjaan
                @foreach ($alokasiHonorBulanMitraKegiatan as $h)
                    {{ucwords(strtolower($h->jenis_honor))}} {{$h->nama_kegiatan}} sebanyak
                    {{$h->target_per_satuan_honor." ".$h->satuan_honor}}
                    @if ($loop->index+1<$alokasiHonorBulanMitraKegiatan->count())
                        dan
                    @endif
                @endforeach

            </span>
        </h3>
        <h3
            style="
            padding-left: 58pt;
            text-indent: -18pt;
            line-height: 150%;
            text-align: left;
            "
        >
            <span class="p">b. </span>
            PIHAK PERTAMA
            <span class="p"
            >telah memeriksa dan menerima dengan baik hasil pekerjaan di
            Badan Pusat Statistik Kabupaten Mempawah dari PIHAK KEDUA.</span
            >
        </h3>

        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p
          style="
            padding-left: 5pt;
            text-indent: 0pt;
            line-height: 151%;
            text-align: left;
          "
        >
          Demikian Berita Acara Serah Terima Pekerjaan ini dibuat agar dipergunakan
          sebagaimana mestinya.
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <p style="text-indent: 0pt; text-align: center">
          Mempawah, {{$tanggalSurat->translatedFormat("d F Y")}}
        </p>
        <p style="text-indent: 0pt; text-align: left"><br /></p>
        <table
            style="border-collapse: collapse; margin-left: 74.7pt"
            cellspacing="0"
            >
            <tr style="height: 79pt">
                <td style="width: 160pt">
                <p
                    class="p"
                    style="
                    padding-left: 2pt;
                    text-indent: 0pt;
                    line-height: 14pt;
                    text-align: center;
                    "
                >
                    <b>PIHAK KEDUA</b><span class="s3">,</span>
                </p>
                <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                <p
                    class="p"
                    style="
                    padding-left: 0pt;
                    text-indent: 0pt;
                    line-height: 14pt;
                    text-align: center;
                    "
                >
                    <b>{{$alokasiHonorBulanMitra->first()->nama_petugas}}</b>
                </p>
                </td>
                <td style="width: 185pt;padding-top: 0pt;">
                <p
                    class="p"
                    style="

                    padding-left: 67pt;
                    text-indent: 0pt;
                    line-height: 14pt;
                    text-align: center;
                    "
                >
                    <b>PIHAK PERTAMA</b><span class="p">,</span>
                </p>
                <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
                <p
                    class="p"
                    style="
                    padding-left: 63pt;
                    text-indent: 0pt;
                    line-height: 14pt;
                    text-align: center;
                    "
                >
                    <b>{{$ppk->nama}}</b>
                </p>
                </td>
            </tr>
        </table>
        <br>
        <br>
        @endforeach
    @endforeach
  </body>
</html>
