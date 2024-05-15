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
      BADAN PUSAT STATISTIK KABUPATEN MEMPAWAH
    </h1>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p
      class="s1"
      style="text-indent: 0pt; text-align: center"
    >
      SURAT TUGAS
    </p>
    <p style="text-indent: 0pt; text-align: center">
      Nomor: B-464/61041/KP.650/05/2024
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
                bahwa sehubungan dengan Kegiatan Survei Perilaku Anti Korupsi
                (SPAK) BPS Kabupaten Mempawah tahun 2024, maka dipandang perlu
                untuk melakukan kegiatan tersebut;
              </p>
            </li>
            <li data-list-text="b.">
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
                bahwa untuk pelaksanaannya perlu dikeluarkan Surat Tugas Kepala
                BPS Kabupaten Mempawah Provinsi Kalimantan Barat untuk Melakukan
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
                  text-indent: -21pt;
                  text-align: justify;
                "
              >
                Undang-Undang No.16 Tahun 1997 tentang Statistik;
              </p>
            </li>
            <li data-list-text="2.">
              <p
                class="s2"
                style="
                  padding-top: 1pt;
                  padding-left: 26pt;
                  padding-right: 2pt;
                  text-indent: -18pt;
                  line-height: 113%;
                  text-align: justify;
                "
              >
                Peraturan Pemerintah No.51 Tahun 1999 tentang Penyelenggaraan
                Statistik;
              </p>
            </li>
            <li data-list-text="3.">
              <p
                class="s2"
                style="
                  padding-left: 26pt;
                  padding-right: 2pt;
                  text-indent: -18pt;
                  line-height: 114%;
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
                  padding-right: 2pt;
                  text-indent: -18pt;
                  line-height: 115%;
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
                Peraturan Kepala Badan Pusat Statistik Nomor 8 Tahun 2020
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
        padding-left: 65pt;
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
            Safira Nurrosyid, S.Tr.Stat.
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
            199606062019122001
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
            Penata Muda Tk I (III/b)
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
            Statistisi Ahli Pertama BPS Kabupaten Mempawah
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
            Melakukan Pengawasan Survei Perilaku Anti Korupsi (SPAK) Tahun 2024
            di Kecamatan Mempawah Timur pada tanggal 14 Mei 2024
          </p>
        </td>
      </tr>
    </table>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
      Mempawah, 13 Mei 2024
    </p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
      Kepala Badan Pusat Statistik <br/> Kabupaten Mempawah
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
      Munawir, SE.,MM.
    </p>
    <p style="padding-left: 283pt; text-indent: 0pt; text-align: center">
      NIP. <span style="color: #1f1f1f">196908031992111001</span>
    </p>
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
              BADAN PUSAT STATISTIK KABUPATEN MEMPAWAH
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
              Lembar ke :1 Kode Nomor : -
            </p>
            <p
              style="
                padding-left: 9pt;
                text-indent: 0pt;
                line-height: 12pt;
                text-align: left;
              "
            >
              Nomor : 163/SPD/05/2024
            </p>
        </div>
    </div>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <p style="text-indent: 0pt; text-align: left"><br /></p>
    <h2 style="padding-left: 64pt; text-indent: 0pt; text-align: center">
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
            Arief Yuandi, SST
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
              padding-right: 124pt;
              text-indent: 0pt;
              line-height: 115%;
              text-align: left;
            "
          >
            Safira Nurrosyid, S.Tr.Stat. NIP. 199606062019122001
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
            Penata Muda Tk I (III/b)
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
            Statistisi Ahli Pertama BPS Kabupaten Mempawah C
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
            Melakukan Pengawasan Survei Perilaku Anti Korupsi (SPAK) Tahun 2024
            di Kecamatan Mempawah Timur pada tanggal 14 Mei 2024
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
            Kendaraan Pribadi
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
            Mempawah
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
            Kecamatan Mempawah Timur
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
            01 hari
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
            14 Mei 2024
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
            14 Mei 2024
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
                BPS Kabupaten Mempawah
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
      Dikeluarkan di : Mempawah <br /> Pada tanggal : 13 Mei 2024
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
      Arief Yuandi, SST
    </p>
    <p style="padding-left: 306pt; text-indent: 0pt; text-align: center">
      NIP. 199306062016021001
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
            Berangkat dari : Mempawah tempat kedudukan
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
            Ke : Kecamatan Mempawah Timur
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
          <p
            class="s2"
            style="padding-left: 9pt; text-indent: 0pt; text-align: left"
          >
            Pada tanggal : 14 Mei 2024
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
            Kepala Badan Pusat Statistik Kabupaten Mempawah
          </p>
          <p style="text-indent: 0pt; text-align: left"><br /></p>
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
            Munawir, SE.,MM.
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
            NIP. <span style="color: #1f1f1f">196908031992111001</span>
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
            II. Tiba di &emsp;&emsp;&emsp;: Kecamatan Mempawah Timur <br/>Pada Tanggal : 14 Mei 2024
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
            Berangkat dari : Kecamatan Mempawah Timur
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
            Ke &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;: Mempawah <br/>
            Pada tanggal &emsp;: 14 Mei 2024
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
            IV. Tiba di : Mempawah (tempat kedudukan)
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
            Pada Tanggal : 14 Mei 2024
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
            Arief Yuandi, SST
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
            NIP. 199306062016021001
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
            Arief Yuandi, SST
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
            NIP. 199306062016021001
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
  </body>
</html>
