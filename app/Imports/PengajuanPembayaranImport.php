<?php

namespace App\Imports;

use App\Models\Sipancong\Pengajuan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class PengajuanPembayaranImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Menangani format tanggal
        $tanggal_pembayaran = null;
        if (!empty($row['tanggal_pembayaran'])) {
            try {
                $tanggal_pembayaran = Carbon::createFromFormat('d/m/Y', $row['tanggal_pembayaran'])->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $tanggal_pembayaran = Carbon::parse($row['tanggal_pembayaran'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal_pembayaran = null;
                }
            }
        }

        // Menangani nilai nominal (yang mungkin dalam format berbeda)
        $nominal_pengajuan = $this->parseNominal($row['nominal_pengajuan']);
        $nominal_dibayarkan = $this->parseNominal($row['nominal_dibayarkan']);
        $nominal_dikembalikan = $this->parseNominal($row['nominal_dikembalikan']);

        return new Pengajuan([
            'nomor_pengajuan' => trim($row['nomor_pengajuan']),
            'sub_fungsi_id' => trim($row['sub_fungsi_id']),
            'nomor_form_pembayaran' => trim($row['nomor_form_pembayaran']),
            'nomor_detail_fa' => trim($row['nomor_detail_fa']),
            'uraian_pengajuan' => trim($row['uraian_pengajuan']),
            'nominal_pengajuan' => trim($nominal_pengajuan),
            'link_folder_dokumen' => trim($row['link_folder_dokumen']),
            'posisi_dokumen_id' => trim($row['posisi_dokumen_id']),
            'status_pengajuan_ppspm_id' => trim($row['status_pengajuan_ppspm_id']),
            'catatan_ppspm' => trim($row['catatan_ppspm']),
            'tanggapan_pengaju_ke_ppspm' => trim($row['tanggapan_pengaju_ke_ppspm']),
            'nominal_dibayarkan' => trim($nominal_dibayarkan),
            'nominal_dikembalikan' => trim($nominal_dikembalikan),
            'status_pembayaran_id' => trim($row['status_pembayaran_id']),
            'tanggal_pembayaran' => trim($tanggal_pembayaran),
            'jenis_dokumen_id' => trim($row['jenis_dokumen_id']),
            'nomor_dokumen' => trim($row['nomor_dokumen']),
        ]);
    }

    /**
     * Parse nominal dari berbagai format
     *
     * @param mixed $value
     * @return float|null
     */
    private function parseNominal($value)
    {
        if (empty($value)) {
            return null;
        }

        // Jika hanya angka tanpa desimal (misalnya '228')
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Jika angka dengan format ribuan seperti '3.520.000'
        if (strpos($value, '.') !== false && !strpos($value, ',')) {
            return (float) str_replace('.', '', $value);
        }

        // Jika angka dengan format desimal seperti '228,000'
        if (strpos($value, ',') !== false) {
            return (float) str_replace([',', '.'], ['point', ''], $value);
        }

        return null;
    }

    /**
     * Validasi data
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nomor_pengajuan' => 'required',
            // Tambahkan aturan validasi lainnya sesuai kebutuhan
        ];
    }
}
