<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = [
            [
                'KODE' => 'VDR.1',
                'NAMA' => 'UMUM',
                'KODE_JENIS_VENDOR' => 'JV.1',
                'BADAN_HUKUM' => 'PT',
                'STATUS' => 'F',
                'TELP_KANTOR' => '-',
                'HP_KANTOR' => '-',
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
                'CP' => '-',
                'JABATAN_CP' => '-',
                'NO_HP_CP' => '-',
                'EMAIL_CP' => '-',
                'NAMA_REKENING' => '-',
                'NO_REKENING' => '-',
                'NAMA_BANK' => '-',
                'ALAMAT_BANK' => '-',
                'PLAFON' => 1,
                'TOP' => '-',
                'PAYMENT' => 'SEBELUM',
                'KETERANGAN_TOP' => '-',
                'FORM_VENDOR' => '(binary)',
                'TGL_AWAL_JADI_VENDOR' => '1970-01-01',
            ],
            [
                'KODE' => 'VDR.2',
                'NAMA' => 'KCB',
                'KODE_JENIS_VENDOR' => 'JV.1',
                'BADAN_HUKUM' => 'PT',
                'STATUS' => 'F',
                'TELP_KANTOR' => '-',
                'HP_KANTOR' => '-',
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
                'CP' => '-',
                'JABATAN_CP' => '-',
                'NO_HP_CP' => '-',
                'EMAIL_CP' => '-',
                'NAMA_REKENING' => '-',
                'NO_REKENING' => '-',
                'NAMA_BANK' => '-',
                'ALAMAT_BANK' => '-',
                'PLAFON' => 1,
                'TOP' => '-',
                'PAYMENT' => 'SEBELUM',
                'KETERANGAN_TOP' => '-',
                'FORM_VENDOR' => '(binary)',
                'TGL_AWAL_JADI_VENDOR' => '1970-01-01',
            ],
            [
                'KODE' => 'VDR.3',
                'NAMA' => 'VENDOR PELAYARAN',
                'KODE_JENIS_VENDOR' => 'JV.3',
                'BADAN_HUKUM' => 'PT',
                'STATUS' => 'F',
                'TELP_KANTOR' => '-',
                'HP_KANTOR' => '-',
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
                'CP' => '-',
                'JABATAN_CP' => '-',
                'NO_HP_CP' => '-',
                'EMAIL_CP' => '-',
                'NAMA_REKENING' => '-',
                'NO_REKENING' => '-',
                'NAMA_BANK' => '-',
                'ALAMAT_BANK' => '-',
                'PLAFON' => 1,
                'TOP' => '-',
                'PAYMENT' => 'SEBELUM',
                'KETERANGAN_TOP' => '-',
                'FORM_VENDOR' => '(binary)',
                'TGL_AWAL_JADI_VENDOR' => '1970-01-01',
            ],
            // Add more vendors as needed
        ];

        DB::table('vendors')->insert($vendors);
    }
}
