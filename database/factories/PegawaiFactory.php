<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai>
 */
class PegawaiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullName = fake()->name();
        $arrName = explode(" ",$fullName);
        $pangkatJabatan = rand(0,3);
        return [
            "nama"=>$fullName,
            "nip"=>rand(1970,2000)."".rand(1000,9999)."".rand(1000,9999)."".rand(1000,9999),
            "nip9"=>rand(100,999)."".rand(100,999)."".rand(100,999),
            "golongan"=>["II","III"][rand(0,1)],
            "pangkat"=>["a","b","c","d"][$pangkatJabatan],
            "jabatan"=>["Muda","Muda Tingkat 1","-","Tingkat 1"][$pangkatJabatan],
            "email"=>strtolower($arrName[0]).".".strtolower($arrName[1])."@bps.go.id",
            "atasan_langsung_id"=>"198008112005021004",
            "unit_kerja"=>"BPS Kabupaten Mempawah",
        ];
    }
}
