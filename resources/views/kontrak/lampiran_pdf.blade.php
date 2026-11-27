<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>LAMPIRAN PERJANJIAN KERJA - {{ $namaBulan }} {{ $tahun }}</title>
    @php
        $number = \Illuminate\Support\Number::class;
        $bil = \Terbilang::class;
        $istilahJabatan = [
            'PML' => 'petugas pemeriksa lapangan',
            'PPL' => 'petugas pendataan lapangan',
            'PETUGAS ENTRI' => 'petugas pengolahan',
        ];
    @endphp
    <style type="text/css">
        @media print {
            .pagebreak {
                page-break-before: always;
            }

            .no-print {
                display: none !important;
            }

            body {
                padding-top: 0 !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
            font-family: "Bookman Old Style", serif;
        }

        body {
            padding-top: 64px;
        }

        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 20px;
            background: #0f172a;
            color: #f8fafc;
            font-family: system-ui, -apple-system, sans-serif;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .print-toolbar * {
            font-family: system-ui, -apple-system, sans-serif;
        }

        .print-toolbar-meta {
            font-size: 13px;
            color: #e2e8f0;
        }

        .print-toolbar-btn {
            background: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .print-toolbar-btn:hover {
            background: #0369a1;
        }

        .screen-lampiran-badge {
            margin: 12px 6pt 6px 6pt;
            padding: 4px 10px;
            background: #f1f5f9;
            border-left: 3px solid #0284c7;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 11px;
            color: #334155;
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

        .s4,
        .s5 {
            color: black;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 12pt;
        }

        table,
        tbody {
            vertical-align: top;
            overflow: visible;
        }
    </style>
</head>

<body>
    <div class="print-toolbar no-print">
        <div class="print-toolbar-meta">
            <strong>Cetak Ulang Lampiran Kontrak</strong> &mdash;
            Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong> |
            Filter: <strong>{{ $labelJenisKegiatan }}</strong> |
            Total: <strong>{{ $lampiranGroups->count() }} Lampiran SPK</strong>
        </div>
        <button type="button" class="print-toolbar-btn" onclick="window.print()">
            Cetak / Print (Ctrl+P)
        </button>
    </div>

    @foreach ($lampiranGroups as $group)
        @php
            $kontrak = $group['kontrak'];
            $mitra = $group['mitra'];
            $alokasiMitra = $group['alokasi'];
            $totalHonor = $group['total_honor'];
            $jenisKegiatan = $group['jenis_kegiatan'];
        @endphp

        <div class="screen-lampiran-badge no-print">
            Lampiran {{ $loop->iteration }}/{{ $loop->count }} &bull;
            {{ $mitra?->nama_1 ?? 'Mitra Tidak Diketahui' }} &bull;
            {{ $jenisKegiatan }}
        </div>

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

        @if (! $loop->last)
            <div class="pagebreak"></div>
        @endif
    @endforeach

</body>

</html>
