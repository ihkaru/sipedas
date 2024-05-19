@php
    $c = Illuminate\Support\Carbon::class;
    $cons = App\Supports\Constants::class;
    $peng = App\Models\Pengaturan::class;
@endphp

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SPPD</title>
    <meta name="author" content="Afif;OpenTBS 1.9.12" />
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
        font-family: Calibri, sans-serif;
        font-style: italic;
        font-weight: bold;
        text-decoration: none;
        font-size: 14pt;
      }
      .s1 {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: bold;
        text-decoration: underline;
        font-size: 10pt;
      }
      p {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
        margin: 0pt;
      }
      .s2 {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      h2 {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: bold;
        text-decoration: none;
        font-size: 10pt;
      }
      .s3 {
        color: #1f1f1f;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      .s4 {
        color: black;
        font-family: Arial, sans-serif;
        font-style: italic;
        font-weight: bold;
        text-decoration: none;
        font-size: 10pt;
      }
      .s5 {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: underline;
        font-size: 10pt;
      }
      .s6 {
        color: black;
        font-family: Calibri, sans-serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      .s7 {
        color: #1f1f1f;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      .s8 {
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: underline;
        font-size: 10pt;
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
        content: counter(c1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
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
        content: counter(d1, decimal) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
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
        content: counter(e1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
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
        content: counter(f1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
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
        content: counter(g1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
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
        content: counter(h1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      #l6 > li:first-child > *:first-child:before {
        counter-increment: h1 0;
      }
      li {
        display: block;
      }
      #l7 {
        padding-left: 0pt;
        counter-reset: i1 1;
      }
      #l7 > li > *:first-child:before {
        counter-increment: i1;
        content: counter(i1, lower-latin) ". ";
        color: black;
        font-family: "Bookman Old Style", serif;
        font-style: normal;
        font-weight: normal;
        text-decoration: none;
        font-size: 10pt;
      }
      #l7 > li:first-child > *:first-child:before {
        counter-increment: i1 0;
      }
      table,
      tbody {
        vertical-align: top;
        overflow: visible;
      }
    </style>
  </head>
  <body>
    <p style=" text-indent: 0pt; text-align: center">
      <span
        ><img
          width="98"
          height="69"
          alt="D:\Logo\BPS-small.png"
          title="D:\Logo\BPS-small.png"
          src="{{asset('SPPD_files/Image_001.png')}}"
      /></span>
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <h1
      style="
        padding-top: 2pt;
        text-indent: 0pt;
        text-align: center;
      "
    >
      BADAN PUSAT STATISTIK {{$namaSatker}}
    </h1>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      class="s1"
      style="text-indent: 0pt; text-align: center"
    >
      SURAT TUGAS
    </p>
    <p style="text-indent: 0pt; text-align: center">
    Nomor: {{$penugasans->suratTugas->nomor_surat_tugas}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <table
      style="border-collapse: collapse; margin-left: 35.324pt"
      cellspacing="0"
    >
      <tr style="height: 101pt">
        <td style="width: 68pt">
          <p
            class="s2"
            style="
              padding-left: 2pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: left;
            "
          >
            Menimbang
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-right: 5pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 365pt">
          <ol id="l1">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-left: 28pt;
                  padding-right: 2pt;
                  text-indent: -21pt;
                  line-height: 114%;
                  text-align: justify;
                "
              >
              &nbsp; bahwa sehubungan dengan {{$penugasans->kegiatan->nama}} BPS {{ucwords(strtolower($namaSatker))}} tahun {{{$c::parse($penugasans->kegiatan->tgl_akhir_perjadin)->year}}}, maka dipandang perlu
                untuk melakukan kegiatan tersebut;
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-left: 28pt;
                  text-indent: -21pt;
                  line-height: 114%;
                  text-align: justify;
                "
              >
              &nbsp;bahwa untuk pelaksanaannya perlu dikeluarkan Surat Tugas Kepala
                BPS {{ucwords(strtolower($namaSatker))}} Provinsi Kalimantan Barat untuk Melakukan
                Kegiatan sebagaimana dimaksud pada poin a di atas.
              </p>
            </li>
          </ol>
        </td>
      </tr>
      <tr style="height: 182pt">
        <td style="width: 68pt">
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 2pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Mengingat
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 5pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 365pt">
          <ol id="l2">
            <li data-list-text="1.">
              <p
                class="s2"
                style="
                  padding-top: 7pt;
                  padding-left: 26pt;
                  text-indent: -18pt;
                  line-height: 12pt;
                  text-align: justify;
                "
              >
              &ensp;Undang-Undang No.16 Tahun 1997 tentang Statistik;
              </p>
            </li>
            <li data-list-text="2.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 26pt;
                  text-indent: -18pt;
                  line-height: 12pt;
                  text-align: justify;
                "
              >
                &ensp;Peraturan Pemerintah No.51 Tahun 1999 tentang Penyelenggaraan Statistik;
              </p>
            </li>
            <li data-list-text="3.">
              <p
                class="s2"
                style="
                  padding-left: 26pt;
                  text-indent: -18pt;
                  line-height: 12pt;
                  text-align: justify;
                "
              >
                Peraturan Pemerintah No.45 Tahun 2013, tentang Tata Cara
                Pelaksanaan Anggaran Pendapatan dan Belanja Negara yang diubah
                dengan Peraturan Pemerintah 50 Tahun 2018 tentang Perubahan atas
                Peraturan Pemerintah Nomor 45 Tahun 2013 tentang Tata Cara
                Pelaksanaan Anggaran Pendapatan dan Belanja Negara;
              </p>
            </li>
            <li data-list-text="4.">
              <p
                class="s2"
                style="
                  padding-left: 26pt;
                  text-indent: -18pt;
                  line-height: 12pt;
                  text-align: justify;
                "
              >
                Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007
                tentang Badan Pusat Statistik;
              </p>
            </li>
            <li data-list-text="5.">
              <p
                class="s2"
                style="
                  padding-left: 26pt;
                  text-indent: -18pt;
                  line-height: 12pt;
                  text-align: justify;
                "
              >
              &ensp;Peraturan Kepala Badan Pusat Statistik Nomor 8 Tahun 2020
              </p>
            </li>
          </ol>
          <p
            class="s2"
            style="
              padding-left: 26pt;
              padding-right: 2pt;
              text-indent: 0pt;
              line-height: 14pt;
              text-align: justify;
            "
          >
            tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan
            Badan Pusat Statistik Kabupaten/Kota.
          </p>
        </td>
      </tr>
    </table>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <h2
      style="
        padding-top: 9pt;
        padding-left: 0;
        text-indent: 0pt;
        text-align: center;
      "
    >
      Memberi Perintah:
    </h2>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <table
      style="border-collapse: collapse; margin-left: 29.924pt"
      cellspacing="0"
    >
      <tr style="height: 13pt">
        <td style="width: 59pt">
          <p
            class="s2"
            style="
              padding-left: 2pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: left;
            "
          >
            Kepada
          </p>
        </td>
        <td style="width: 41pt">
          <p
            class="s2"
            style="
              padding-left: 2pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: center;
            "
          >
            :
          </p>
        </td>
        <td style="width: 88pt">
          <p
            class="s2"
            style="
              padding-left: 17pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: left;
            "
          >
            Nama
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-right: 5pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 250pt">
          <p
            class="s2"
            style="
              padding-left: 5pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: left;
            "
          >
            {{$penugasans->pegawai->nama}}
          </p>
        </td>
      </tr>
      <tr style="height: 14pt">
        <td style="width: 59pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 41pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 88pt">
          <p
            class="s2"
            style="
              padding-left: 17pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: left;
            "
          >
            NIP
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-right: 5pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 250pt">
          <p
            class="s2"
            style="
              padding-left: 5pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: left;
            "
          >
          {{$penugasans->pegawai->nip}}
          </p>
        </td>
      </tr>
      <tr style="height: 13pt">
        <td style="width: 59pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 41pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 88pt">
          <p
            class="s2"
            style="
              padding-left: 17pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: left;
            "
          >
            Pangkat/Gol
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-right: 5pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 250pt">
          <p
            class="s2"
            style="
              padding-left: 5pt;
              text-indent: 0pt;
              line-height: 11pt;
              text-align: left;
            "
          >
            {{$penugasans->pegawai->pangkat_golongan}}
          </p>
        </td>
      </tr>
      <tr style="height: 31pt">
        <td style="width: 59pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 41pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td style="width: 88pt">
          <p
            class="s2"
            style="
              padding-top: 5pt;
              padding-left: 17pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Jabatan
          </p>
        </td>
        <td style="width: 17pt">
          <p
            class="s2"
            style="
              padding-top: 5pt;
              padding-right: 5pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            :
          </p>
        </td>
        <td style="width: 250pt">
          <p
            class="s2"
            style="
              padding-left: 5pt;
              padding-right: 1pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{$penugasans->pegawai->jabatan}} BPS {{ucwords(strtolower($namaSatker))}}
          </p>
        </td>
      </tr>
      <tr style="height: 33pt">
        <td style="width: 59pt">
          <p
            class="s2"
            style="
              padding-top: 6pt;
              padding-left: 2pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Untuk
          </p>
        </td>
        <td style="width: 41pt">
          <p
            class="s2"
            style="
              padding-top: 6pt;
              padding-left: 2pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            :
          </p>
        </td>
        <td style="width: 355pt" colspan="3">
          <p
            class="s2"
            style="
              padding-top: 4pt;
              padding-left: 17pt;
              text-indent: 0pt;
              line-height: 14pt;
              text-align: left;
            "
          >
            Melaksanakan {{$penugasans->kegiatan->nama}} Tahun {{$c::parse($penugasans->kegiatan->tgl_akhir_perjadin)->year}}
            @if ($penugasans->jenis_surat_tugas != $cons::NON_SPPD)
            di
                @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                    Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
                @endif
                @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                    Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}},
                    Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
                @endif
                @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                    Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                    Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}},
                    {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
                @endif
                pada tanggal
                @if ($penugasans->tgl_mulai_tugas == $penugasans->tgl_mulai_tugas)
                    {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')}}
                @else
                    {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')." s.d. ".$c::parse($penugasans->tgl_akhir_tugas)->translatedFormat('d M Y')}}
                @endif

            @endif
          </p>
        </td>
      </tr>
    </table>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
      {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}}, {{$c::parse($penugasans->tgl_pengajuan_tugas)->translatedFormat('d F Y')}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
        @if($peng::key('ID_PLH_DEFAULT')->nilai != $penugasans->plh->nip)
            An.
        @endif
      Kepala Badan Pusat Statistik <br/> {{ucwords(strtolower($namaSatker))}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /><br /></p>
    <p
      class="s3"
      style="
        padding-top: 9pt;
        padding-left: 283pt;
        text-indent: 0pt;
        text-align: center;
      "
    >
      {{$penugasans->plh->nama}}
    </p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
      NIP. <span style="color: #1f1f1f">{{$penugasans->plh->nip}}</span>
    </p>
    @if ($penugasans->jenis_surat_tugas != $cons::NON_SPPD)
    <div class="pagebreak"> </div>

    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <div style="display: flex;">
        <div style="max-width: 300px; display: flex">
            <div>
                <span><img
                    width="94"
                    height="71"
                    alt="D:\Logo\BPS-small.png"
                    title="D:\Logo\BPS-small.png"
                    src="{{asset('SPPD_files/Image_002.png')}}"
                /></span>
            </div>
            <div
              class="s4"
              style="
                text-indent: 0pt;
                line-height: 107%;
                text-align: left;
                align-self: center;
                padding-left: 10px;
              "
            >
              BADAN PUSAT STATISTIK {{$namaSatker}}
            </div>
        </div>
        <div style="width: 200px;"></div>
        <div style="text-align: right;">
            <p
              style="
                padding-top: 9pt;
                padding-left: 9pt;
                text-indent: 0pt;
                line-height: 115%;
                text-align: left;
              "
            >
              Lembar ke :1 <br/>Kode Nomor : -
            </p>
            <p
              style="
                padding-left: 9pt;
                text-indent: 0pt;
                line-height: 12pt;
                text-align: left;
              "
            >
              Nomor : {{$penugasans->suratPerjadin->nomor_surat_perjadin}}
            </p>
        </div>
    </div>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <h2 style="padding-left: 0; text-indent: 0pt; text-align: center">
      SURAT PERJALANAN DINAS (SPD)
    </h2>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <table
      style="border-collapse: collapse; margin-left: 5.64998pt"
      cellspacing="0"
    >
      <tr style="height: 29pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            1.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Pejabat Pembuat Komitmen
          </p>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{$ppk->nama}}
          </p>
        </td>
      </tr>
      <tr style="height: 36pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            2.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              line-height: 115%;
              text-align: left;
            "
          >
            Nama / NIP pegawai yang melaksanakan perjalanan dinas
          </p>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              line-height: 115%;
              text-align: left;
            "
          >
            {{$penugasans->pegawai->nama}} <br />
            NIP. {{$penugasans->pegawai->nip}}
          </p>
        </td>
      </tr>
      <tr style="height: 48pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            3.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <ol id="l3">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Golongan/Pangkat
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Jabatan/Instansi
              </p>
            </li>
            <li data-list-text="c.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Tingkat Biaya Perjalanan Dinas
              </p>
            </li>
          </ol>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{$penugasans->pegawai->pangkat_golongan}}
          </p>
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              padding-right: 22pt;
              text-indent: 0pt;
              line-height: 113%;
              text-align: left;
            "
          >
          {{$penugasans->pegawai->jabatan}} BPS {{ucwords(strtolower($namaSatker))}} <br/>
          C
          </p>
        </td>
      </tr>
      <tr style="height: 56pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            4.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Maksud perjalanan dinas
          </p>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              padding-right: 6pt;
              text-indent: 0pt;
              line-height: 114%;
              text-align: justify;
            "
          >
          Melaksanakan {{$penugasans->kegiatan->nama}} Tahun {{$c::parse($penugasans->kegiatan->tgl_akhir_perjadin)->year}}
          @if ($penugasans->jenis_surat_tugas != $cons::NON_SPPD)
          di
              @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                  Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
              @endif
              @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                  Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}},
                  Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
              @endif
              @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                  Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                  Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}},
                  {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
              @endif
              pada tanggal
              @if ($penugasans->tgl_mulai_tugas == $penugasans->tgl_mulai_tugas)
                  {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')}}
              @else
                  {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')." s.d. ".$c::parse($penugasans->tgl_akhir_tugas)->translatedFormat('d M Y')}}
              @endif

          @endif
          </p>
        </td>
      </tr>
      <tr style="height: 29pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            5.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Alat angkutan yang dipergunakan
          </p>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
          {{$cons::JENIS_TRANSPORTASI_OPTIONS[($penugasans->transportasi)]}}
          </p>
        </td>
      </tr>
      <tr style="height: 30pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            6.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <ol id="l4">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Tempat berangkat
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Tempat tujuan
              </p>
            </li>
          </ol>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}}
          </p>
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
          </p>
        </td>
      </tr>
      <tr style="height: 56pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            7.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <ol id="l5">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-top: 7pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Lamanya perjalanan dinas
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Tanggal berangkat
              </p>
            </li>
            <li data-list-text="c.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Tanggal Kembali
              </p>
            </li>
          </ol>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{$penugasans->lama_perjadin}} hari
          </p>
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')}}
          </p>
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
          {{$c::parse($penugasans->tgl_akhir_tugas)->translatedFormat('d F Y')}}
          </p>
        </td>
      </tr>
      <tr style="height: 17pt">
        <td
          style="
            width: 26pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          rowspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 8pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            8.
          </p>
        </td>
        <td
          style="
            width: 65pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Pengikut:
          </p>
        </td>
        <td
          style="
            width: 151pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Nama
          </p>
        </td>
        <td
          style="
            width: 144pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Tanggal Lahir
          </p>
        </td>
        <td
          style="
            width: 139pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Keterangan
          </p>
        </td>
      </tr>
      <tr style="height: 30pt">
        <td
          style="
            width: 65pt;
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
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            1.
          </p>
          <p
            class="s2"
            style="
              padding-top: 1pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            2.
          </p>
        </td>
        <td
          style="
            width: 151pt;
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
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td
          style="
            width: 144pt;
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
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
        <td
          style="
            width: 139pt;
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
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
      </tr>
      <tr style="height: 56pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 7pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            9.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Pembeban anggaran
          </p>
          <ol id="l6">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Instansi
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                Mata anggaran
              </p>
            </li>
          </ol>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <ol id="l7">
            <li data-list-text="a.">
              <p
                class="s2"
                style="
                  padding-top: 8pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                BPS {{ucwords(strtolower($namaSatker))}}
              </p>
            </li>
            <li data-list-text="b.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 19pt;
                  text-indent: -12pt;
                  text-align: left;
                "
              >
                ...
              </p>
            </li>
          </ol>
        </td>
      </tr>
      <tr style="height: 29pt">
        <td
          style="
            width: 26pt;
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
            class="s2"
            style="
              padding-top: 7pt;
              padding-right: 4pt;
              text-indent: 0pt;
              text-align: right;
            "
          >
            10.
          </p>
        </td>
        <td
          style="
            width: 216pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 7pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Keterangan lain-lain
          </p>
        </td>
        <td
          style="
            width: 283pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p style="text-indent: 0pt; text-align: left"><br /></p>
        </td>
      </tr>
    </table>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      style="
        padding-left: 350pt;
        text-indent: 0pt;
        line-height: 108%;
        text-align: left;
      "
    >
      Dikeluarkan di : {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}} <br /> Pada tanggal : {{$c::parse($penugasans->tgl_pengajuan_tugas)->translatedFormat('d F Y')}}

    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 306pt; text-indent: 0pt; text-align: center">
      Pejabat Pembuat Komitmen
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /><br /></p>
    <p
      class="s5"
      style="
        padding-top: 8pt;
        padding-left: 306pt;
        text-indent: 0pt;
        text-align: center;
      "
    >
      {{$ppk->nama}}
    </p>
    <p style="padding-left: 306pt; text-indent: 0pt; text-align: center">
      NIP. {{$ppk->nip}}
    </p>
    <div class="pagebreak"> </div>
    <table
      style="border-collapse: collapse; margin-left: 10.09pt"
      cellspacing="0"
    >
      <tr style="height: 205pt">
        <td
          style="
            width: 262pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 9pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            I<span class="s6">.</span>
          </p>
        </td>
        <td
          style="
            width: 255pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 9pt;
              padding-right: 70pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Berangkat dari : {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}} tempat kedudukan
          </p>
          <p
            class="s2"
            style="
              padding-left: 103pt;
              padding-right: 38pt;
              text-indent: -93pt;
              text-align: left;
            "
          >
            Ke :
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="padding-left: 9pt; text-indent: 0pt; text-align: left"
          >
            Pada tanggal : {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')}}
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            Mengetahui,
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
          @if($peng::key('ID_PLH_DEFAULT')->nilai != $penugasans->plh->nip)
          An.
          @endif
          Kepala Badan Pusat Statistik <br/> {{ucwords(strtolower($namaSatker))}}
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /><br /></p>
          <p
            class="s7"
            style="
              padding-top: 9pt;
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            {{$penugasans->plh->nama}}
          </p>
          <p
            class="s2"
            style="
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            NIP. <span style="color: #1f1f1f">{{$penugasans->plh->nip}}</span>
          </p>
        </td>
      </tr>
      <tr style="height: 147pt">
        <td
          style="
            width: 262pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 20pt;
              padding-right: 9pt;
              text-indent: -12pt;
              line-height: 199%;
              text-align: left;
            "
          >
            II. Tiba di &emsp;&emsp;&emsp;:
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
            <br/>Pada Tanggal :
            {{$c::parse($penugasans->tgl_mulai_tugas)->translatedFormat('d F Y')}}
          </p>
        </td>
        <td
          style="
            width: 255pt;
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
            class="s2"
            style="
            padding-top: 8pt;
            padding-left: 20pt;
            padding-right: 9pt;
            text-indent: -12pt;
            line-height: 199%;
            text-align: left;
            "
          >
            Berangkat dari :
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KABUPATEN_KOTA)
                Kabupaten {{ucwords(strtolower($penugasans->kabkot->kabkot))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_KECAMATAN)
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
            @if ($penugasans->level_tujuan_penugasan == $cons::LEVEL_PENUGASAN_DESA_KELURAHAN)
                Desa/Kelurahan {{ucwords(strtolower($penugasans->desa->desa_kel))}},
                Kecamatan {{ucwords(strtolower($penugasans->kecamatan->kecamatan))}}
            @endif
          </p>
          <p
            class="s2"
            style="
              padding-left: 9pt;
              padding-right: 90pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Ke &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;: {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}} <br/>
            Pada tanggal &emsp;:
            {{$c::parse($penugasans->tgl_akhir_tugas)->translatedFormat('d F Y')}}
          </p>
        </td>
      </tr>
      <tr style="height: 135pt">
        <td
          style="
            width: 262pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 27pt;
              padding-right: 127pt;
              text-indent: -18pt;
              text-align: left;
            "
          >
            III. Tiba di &emsp;&emsp;&emsp;&emsp;:
            <br/>
            Pada Tanggal&emsp;:

          </p>
        </td>
        <td
          style="
            width: 255pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 9pt;
              text-indent: 0pt;
              text-align: left;
            "
          >
            Berangkat dari :
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="padding-left: 9pt; text-indent: 0pt; text-align: left"
          >
            Ke :
          </p>
          <p
            class="s2"
            style="padding-left: 9pt; text-indent: 0pt; text-align: left"
          >
            Pada tanggal :
          </p>
        </td>
      </tr>
      <tr style="height: 182pt">
        <td
          style="
            width: 262pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 27pt;
              padding-right: 127pt;
              text-indent: -18pt;
              text-align: left;
            "
          >
            IV. Tiba di : {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}} (tempat kedudukan)
          </p>
          <p
            class="s2"
            style="
              padding-left: 26pt;
              text-indent: 0pt;
              line-height: 12pt;
              text-align: left;
            "
          >
            Pada Tanggal :
            {{$c::parse($penugasans->tgl_akhir_tugas)->translatedFormat('d F Y')}}
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-top: 9pt;
              padding-left: 60pt;
              padding-right: 62pt;
              text-indent: 40pt;
              text-align: left;
            "
          >
            Mengetahui, <br /> Pejabat Pembuat Komitmen
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /><br /><br /><br /></p>
          <p
            class="s8"
            style="
              padding-top: 8pt;
              padding-left: 62pt;
              padding-right: 62pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            {{$ppk->nama}}
          </p>
          <p
            class="s2"
            style="
              padding-left: 62pt;
              padding-right: 62pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            NIP. {{$ppk->nip}}

          </p>
        </td>
        <td
          style="
            width: 255pt;
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
            class="s2"
            style="
              padding-top: 8pt;
              padding-left: 10pt;
              padding-right: 9pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            Telah diperiksa dengan keterangan bahwa penjelasan tersebut atas
            perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu
            singkat,
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-left: 14pt;
              padding-right: 13pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            Pejabat yang berwenang/pejabat lainnya yang ditunjuk
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            Pejabat Pembuat Komitmen
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /><br /></p>
          <p
            class="s8"
            style="
              padding-top: 9pt;
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            {{$ppk->nama}}
          </p>
          <p
            class="s2"
            style="
              padding-left: 55pt;
              padding-right: 55pt;
              text-indent: 0pt;
              text-align: center;
            "
          >
            NIP. {{$ppk->nip}}

          </p>
        </td>
      </tr>
      <tr style="height: 76pt">
        <td
          style="
            width: 517pt;
            border-top-style: solid;
            border-top-width: 1pt;
            border-left-style: solid;
            border-left-width: 1pt;
            border-bottom-style: solid;
            border-bottom-width: 1pt;
            border-right-style: solid;
            border-right-width: 1pt;
          "
          colspan="2"
        >
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="
              padding-top: 7pt;
              padding-left: 105pt;
              padding-right: 9pt;
              text-indent: -96pt;
              text-align: justify;
            "
          >
            V. Catatan Lain: PPK yang menertibkan SPD, pegawai yang melakukan
            perjalanan dinas, pejabat yang mengesahkan tanggal berangkat/tiba,
            serta bendahara pengeluaran bertanggung jawab berdasarkan
            peraturan-peraturan keuangan Negara apabila Negara menderita rugi
            akibat kesalahan, kelalaian, dan kealpaannya.
          </p>
        </td>
      </tr>
    </table>
    @endif
    @if ($penugasans->transportasi != $cons::TRANSPORTASI_KENDARAAN_DINAS)
        <div class="pagebreak"></div>
        <h2
        style="
            padding-top: 3pt;
            text-indent: 0pt;
            text-align: center;
            /* font-weight:bold; */
        "
        >
        SURAT PERNYATAAN
        </h2>
    <h2
      style="
        padding-top: 9pt;
        text-indent: 0pt;
        text-align: center;
      "
    >
      TIDAK MENGGUNAKAN KENDARAAN DINAS
    </h2>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 6pt; text-indent: 0pt; text-align: justify">
      Yang bertanda tangan di bawah ini:
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 42pt; text-indent: 0pt; text-align: left">
      Nama &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;: {{$penugasans->pegawai->nama}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 42pt; text-indent: 0pt; text-align: left">
      NIP &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&nbsp;&ensp;&ensp;&ensp;&nbsp;: {{$penugasans->pegawai->nip}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      style="
        padding-left: 42pt;
        text-indent: 0pt;
        text-align: left;
      "
    >
      Pangkat/Golongan &ensp;: {{$penugasans->pegawai->pangkat_golongan}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 42pt; text-indent: 0pt; text-align: left">
        Jabatan &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&nbsp;: {{$penugasans->pegawai->jabatan}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 42pt; text-indent: 0pt; text-align: left">
        Unit Kerja &ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&nbsp;&ensp;&ensp;: {{$penugasans->pegawai->unit_kerja}}
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      style="
        padding-left: 5pt;
        text-indent: 0pt;
        line-height: 150%;
        text-align: justify;
      "
    >
      Menerangkan bahwa dalam rangka melaksanakan {{strtolower($penugasans->jenis_perjadin)}}
      untuk melaksanakan tugas kedinasan sesuai surat tugas, saya benar-benar
      tidak menggunakan kendaraan dinas.
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      style="
        padding-left: 5pt;
        text-indent: 0pt;
        line-height: 150%;
        text-align: justify;
      "
    >
      Demikian pernyataan ini kami buat dengan sebenar-benarnya untuk digunakan
      sebagaimana mestinya.
      <i
        >Apabila terdapat kekeliruan dalam pertanggungjawaban SPD dan
        mengakibatkan kerugian negara, saya bersedia dituntut sesuai peraturan
        yang berlaku dan mengembalikan biaya transport lokal yang sudah
        terlanjur saua terima ke kas negara.</i
      >
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <table
      style="border-collapse: collapse; margin-left: 262.94pt"
      cellspacing="0"
    >
      <tr style="height: 13pt">
        <td style="width: 143pt">
          <p
            style="
            padding-left: 10pt;
            padding-right: 10pt;
            text-indent: 2pt;
            line-height: 13pt;
            text-align: center;
            "
          >
          {{ucwords(strtolower($namaSatkerTanpaLevelAdministrasi))}}, {{$c::parse($penugasans->tgl_pengajuan_tugas)->translatedFormat('d F Y')}}

          </p>
        </td>
      </tr>
      <tr style="height: 59pt">
        <td style="width: 143pt">
          <p
            style="
            padding-left: 10pt;
            padding-right: 10pt;
            text-indent: 2pt;
            line-height: 13pt;
            text-align: center;
            "
          >
            Pelaksana {{$penugasans->jenis_perjadin}}
          </p>
        </td>
      </tr>
      <tr style="height: 57pt">
        <td style="width: 300pt">
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            style="
              padding-left: 10pt;
              padding-right: 10pt;
              text-indent: 2pt;
              line-height: 13pt;
              text-align: center;
            "
          >
            {{$penugasans->pegawai->nama}} <br/>
          </p>
          <p
            style="
              padding-left: 10pt;
              padding-right: 10pt;
              text-indent: 2pt;
              line-height: 13pt;
              text-align: center;
            "
          >
            NIP. {{$penugasans->pegawai->nip}}
          </p>
        </td>
      </tr>
    </table>
    @endif
  </body>
</html>
