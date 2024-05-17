
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
<div>

    @php
        $alokasiHonorBulan = $alokasiHonor->where('tahun_akhir_kegiatan',$tahun)->where('bulan_akhir_kegiatan',$bulan);
        $idSobat = $alokasiHonorBulan->pluck('id_sobat')->unique()->flatten();
    @endphp

    @foreach ($idSobat as $s)
            @php
                $alokasiHonorBulanMitra = $alokasiHonorBulan->where('id_sobat',$s);
                $idKegiatan = $alokasiHonorBulanMitra->pluck('id_kegiatan')->unique()->flatten();
            @endphp
        <p>
            Nama: {{$alokasiHonorBulanMitra->first()->nama_petugas}} <br>
            NIK: {{$alokasiHonorBulanMitra->first()->nik}} <br>
            ID SOBAT: {{$alokasiHonorBulanMitra->first()->id_sobat}} <br><br><br>

            @foreach ($idKegiatan as $k)
                @php
                    $alokasiHonorBulanMitraKegiatan = $alokasiHonorBulanMitra->where('id_kegiatan',$k);
                @endphp
                <p>
                    Nama Kegiatan: {{$alokasiHonorBulanMitraKegiatan->first()->id_kegiatan}} <br>
                    Jabatan: {{$alokasiHonorBulanMitraKegiatan->first()->jabatan}} <br>
                    Satuan Honor: {{$alokasiHonorBulanMitraKegiatan->first()->satuan_honor}} <br>
                    Target per Satuan Honor: {{$alokasiHonorBulanMitraKegiatan->first()->target_per_satuan_honor}} <br>
                    Honor per Satuan Honor: {{$alokasiHonorBulanMitraKegiatan->first()->honor_per_satuan_honor}} <br>
                    Target Honor: {{$alokasiHonorBulanMitraKegiatan->first()->target_honor}} <br>
                    <br>
                    <br>
                </p>
            @endforeach
        </p>
        <br><br><br><br>
        <div class="pagebreak"> </div>
    @endforeach
</div>
