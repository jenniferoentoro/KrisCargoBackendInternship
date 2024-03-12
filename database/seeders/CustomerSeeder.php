<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = [
            [
                'KODE' => 'MIT.UMUM',
                'KODE_GROUP' => 'GM.NONE',
                'NAMA' => 'UMUM',
                'KODE_USAHA' => 'JU.1',
                'BADAN_HUKUM' => 'PT',
                'JENIS' => 'F',
                'TELP' => '-',
                'HP' => '-',
                'WEBSITE' => '-',
                'EMAIL' => '-',
                'NO_KTP' => '-',
                'NAMA_KTP' => '-',
                'ALAMAT_KTP' => '-',
                'RT_KTP' => '-',
                'RW_KTP' => '-',
                'KELURAHAN_KTP' => '-',
                'KECAMATAN_KTP' => '-',
                'KODE_KOTA_KTP' => 'KOT.1',
                'FOTO_KTP' => '(binary)',
                'NO_NPWP' => '-',
                'NAMA_NPWP' => '-',
                'ALAMAT_NPWP' => '-',
                'RT_NPWP' => '-',
                'RW_NPWP' => '-',
                'KELURAHAN_NPWP' => '-',
                'KECAMATAN_NPWP' => '-',
                'KODE_KOTA_NPWP' => 'KOT.1',
                'FOTO_NPWP' => '(binary)',
                'CONTACT_PERSON_1' => '-',
                'JABATAN_1' => '-',
                'NO_HP_1' => '-',
                'EMAIL_1' => '-',
                'CONTACT_PERSON_2' => '-',
                'JABATAN_2' => '-',
                'NO_HP_2' => '-',
                'EMAIL_2' => '-',
                'KODE_AR' => '2',
                'KODE_SALES' => '1',
                'DIBAYAR' => 'PENGIRIM',
                'LOKASI' => 'POL',
                'PLAFON' => 1,
                'TOP' => '1',
                'PAYMENT' => 'SEBELUM BONGKAR',
                'KETERANGAN_TOP' => '1',
                'FORM_CUSTOMER' => '(binary)',
                'TGL_REG' => '1970-01-01',

            ],
            [
                'KODE' => 'MIT.KCB',
                'KODE_GROUP' => 'GM.NONE',
                'NAMA' => 'KCB',
                'KODE_USAHA' => 'JU.1',
                'BADAN_HUKUM' => 'PT',
                'JENIS' => 'F',
                'TELP' => '-',
                'HP' => '-',
                'WEBSITE' => '-',
                'EMAIL' => '-',
                'NO_KTP' => '-',
                'NAMA_KTP' => '-',
                'ALAMAT_KTP' => '-',
                'RT_KTP' => '-',
                'RW_KTP' => '-',
                'KELURAHAN_KTP' => '-',
                'KECAMATAN_KTP' => '-',
                'KODE_KOTA_KTP' => 'KOT.1',
                'FOTO_KTP' => '(binary)',
                'NO_NPWP' => '-',
                'NAMA_NPWP' => '-',
                'ALAMAT_NPWP' => '-',
                'RT_NPWP' => '-',
                'RW_NPWP' => '-',
                'KELURAHAN_NPWP' => '-',
                'KECAMATAN_NPWP' => '-',
                'KODE_KOTA_NPWP' => 'KOT.1',
                'FOTO_NPWP' => '(binary)',
                'CONTACT_PERSON_1' => '-',
                'JABATAN_1' => '-',
                'NO_HP_1' => '-',
                'EMAIL_1' => '-',
                'CONTACT_PERSON_2' => '-',
                'JABATAN_2' => '-',
                'NO_HP_2' => '-',
                'EMAIL_2' => '-',
                'KODE_AR' => '2',
                'KODE_SALES' => '1',
                'DIBAYAR' => 'PENGIRIM',
                'LOKASI' => 'POL',
                'PLAFON' => 1,
                'TOP' => '1',
                'PAYMENT' => 'SEBELUM BONGKAR',
                'KETERANGAN_TOP' => '1',
                'FORM_CUSTOMER' => '(binary)',
                'TGL_REG' => '1970-01-01',

            ]
            // Add more companies as needed
        ];

        DB::table('customers')->insert($companies);
    }
}
