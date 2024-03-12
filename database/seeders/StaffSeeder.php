<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Staff::create([
            'EMAIL' => 'admin@gmail.com',
            'NAMA' => 'Admin',
            'NICKNAME' => 'JD',
            'KODE_JABATAN' => 'JBT.6',
            'KODE_LOKASI' => 'LOK.1',

            'NIK' => '1234567891',
            'NO_SIM' => '9876543211',
            'ALAMAT_KTP' => 'KTP Address',
            'ALAMAT_DOMISILI' => 'Residential Address',
            'TTL' => '1990-01-01',
            'JENIS_KELAMIN' => 'LAKI-LAKI',
            'AGAMA' => 'ISLAM',
            'STATUS_PERNIKAHAN' => 'BELUM MENIKAH',
            'JUMLAH_ANAK' => 2,
            'NO_HP' => '1234567890',
            'NO_HP_KANTOR' => '0987654321',
            'NO_HP_KELUARGA' => '9876543210',
            'KETERANGAN_KELUARGA' => 'Family Information',
            'TGL_MULAI_KERJA' => '2020-01-01',
            'TGL_SELESAI_KONTRAK' => '2025-12-31',
            'STATUS_KARYAWAN' => 'HARIAN',
            'JAM_MASUK' => '08:00:00',
            'JAM_KELUAR' => '17:00:00',
            'ACCOUNT_NUMBER' => '1234567890',
            'BANK' => 'Bank Name',
            'ATAS_NAMA' => 'Account Holder',
            'email_verified_at' => null,
            'GAJI_POKOK' => 'BULANAN',
            'BPJS_KESEHATAN' => 'MANDIRI',
            'BPJS_KETENAGAKERJAAN' => 'TIDAK ADA',
            'UANG_MAKAN' => 'HARIAN',
            'UANG_TRANSPORT' => 'TIDAK ADA',
            'UANG_LEMBUR' => 'TIDAK ADA',
            'PULSA' => 'BULANAN',
            'INSENTIF' => 'TIDAK ADA',
            'THR' => 'TAHUNAN',
            'TUNJANGAN_KENDARAAN' => 'TIDAK ADA',
            'TUNJANGAN_LAIN' => 'ADA',
            'DET_GAJI_POKOK' => 1,
            'DET_BPJS_KESEHATAN' => 2,
            'DET_BPJS_KETENAGAKERJAAN' => 3,
            'DET_UANG_MAKAN' => 4,
            'DET_UANG_TRANSPORT' => 5,
            'DET_UANG_LEMBUR' => 6,
            'DET_PULSA' => 7,
            'DET_TUNJANGAN_KENDARAAN' => 8,
            'DET_TUNJANGAN_LAIN' => 9,
            'FOTO_KTP' => null,
            'FOTO_SIM' => null,
            'FOTO_KK' => null,
            'FOTO_BPJS_KESEHATAN' => null,
            'FOTO_BPJS_KETENAGAKERJAAN' => null,
            'FOTO_KARYAWAN' => null,
            'FOTO_KONTRAK_KERJA' => null,
        ]);

        Staff::create([
            'EMAIL' => 'ar@gmail.com',
            'NAMA' => 'AR Account',
            'NICKNAME' => 'JD',
            'KODE_JABATAN' => 'JBT.7',
            'KODE_LOKASI' => 'LOK.1',
            'NIK' => '1234567892',
            'NO_SIM' => '9876543212',
            'ALAMAT_KTP' => 'KTP Address',
            'ALAMAT_DOMISILI' => 'Residential Address',
            'TTL' => '1990-01-01',
            'JENIS_KELAMIN' => 'PEREMPUAN',
            'AGAMA' => 'ISLAM',
            'STATUS_PERNIKAHAN' => 'BELUM MENIKAH',
            'JUMLAH_ANAK' => 2,
            'NO_HP' => '1234567890',
            'NO_HP_KANTOR' => '0987654321',
            'NO_HP_KELUARGA' => '9876543210',
            'KETERANGAN_KELUARGA' => 'Family Information',
            'TGL_MULAI_KERJA' => '2020-01-01',
            'TGL_SELESAI_KONTRAK' => '2025-12-31',
            'STATUS_KARYAWAN' => 'HARIAN',
            'JAM_MASUK' => '08:00:00',
            'JAM_KELUAR' => '17:00:00',
            'ACCOUNT_NUMBER' => '1234567890',
            'BANK' => 'Bank Name',
            'ATAS_NAMA' => 'Account Holder',
            'email_verified_at' => null,
            'GAJI_POKOK' => 'BULANAN',
            'BPJS_KESEHATAN' => 'MANDIRI',
            'BPJS_KETENAGAKERJAAN' => 'TIDAK ADA',
            'UANG_MAKAN' => 'HARIAN',
            'UANG_TRANSPORT' => 'TIDAK ADA',
            'UANG_LEMBUR' => 'TIDAK ADA',
            'PULSA' => 'BULANAN',
            'INSENTIF' => 'TIDAK ADA',
            'THR' => 'TAHUNAN',
            'TUNJANGAN_KENDARAAN' => 'TIDAK ADA',
            'TUNJANGAN_LAIN' => 'ADA',
            'DET_GAJI_POKOK' => 1,
            'DET_BPJS_KESEHATAN' => 2,
            'DET_BPJS_KETENAGAKERJAAN' => 3,
            'DET_UANG_MAKAN' => 4,
            'DET_UANG_TRANSPORT' => 5,
            'DET_UANG_LEMBUR' => 6,
            'DET_PULSA' => 7,
            'DET_TUNJANGAN_KENDARAAN' => 8,
            'DET_TUNJANGAN_LAIN' => 9,
            'FOTO_KTP' => null,
            'FOTO_SIM' => null,
            'FOTO_KK' => null,
            'FOTO_BPJS_KESEHATAN' => null,
            'FOTO_BPJS_KETENAGAKERJAAN' => null,
            'FOTO_KARYAWAN' => null,
            'FOTO_KONTRAK_KERJA' => null,
        ]);

        Staff::create([
            'EMAIL' => 'sales@gmail.com',
            'NAMA' => 'Sales Account',
            'NICKNAME' => 'JD',
            'KODE_JABATAN' => 'JBT.6',
            'KODE_LOKASI' => 'LOK.1',
            'NIK' => '1234567893',
            'NO_SIM' => '9876543213',
            'ALAMAT_KTP' => 'KTP Address',
            'ALAMAT_DOMISILI' => 'Residential Address',
            'TTL' => '1990-01-01',
            'JENIS_KELAMIN' => 'PEREMPUAN',
            'AGAMA' => 'ISLAM',
            'STATUS_PERNIKAHAN' => 'BELUM MENIKAH',
            'JUMLAH_ANAK' => 2,
            'NO_HP' => '1234567890',
            'NO_HP_KANTOR' => '0987654321',
            'NO_HP_KELUARGA' => '9876543210',
            'KETERANGAN_KELUARGA' => 'Family Information',
            'TGL_MULAI_KERJA' => '2020-01-01',
            'TGL_SELESAI_KONTRAK' => '2025-12-31',
            'STATUS_KARYAWAN' => 'HARIAN',
            'JAM_MASUK' => '08:00:00',
            'JAM_KELUAR' => '17:00:00',
            'ACCOUNT_NUMBER' => '1234567890',
            'BANK' => 'Bank Name',
            'ATAS_NAMA' => 'Account Holder',
            'email_verified_at' => null,
            'GAJI_POKOK' => 'BULANAN',
            'BPJS_KESEHATAN' => 'MANDIRI',
            'BPJS_KETENAGAKERJAAN' => 'TIDAK ADA',
            'UANG_MAKAN' => 'HARIAN',
            'UANG_TRANSPORT' => 'TIDAK ADA',
            'UANG_LEMBUR' => 'TIDAK ADA',
            'PULSA' => 'BULANAN',
            'INSENTIF' => 'TIDAK ADA',
            'THR' => 'TAHUNAN',
            'TUNJANGAN_KENDARAAN' => 'TIDAK ADA',
            'TUNJANGAN_LAIN' => 'ADA',
            'DET_GAJI_POKOK' => 1,
            'DET_BPJS_KESEHATAN' => 2,
            'DET_BPJS_KETENAGAKERJAAN' => 3,
            'DET_UANG_MAKAN' => 4,
            'DET_UANG_TRANSPORT' => 5,
            'DET_UANG_LEMBUR' => 6,
            'DET_PULSA' => 7,
            'DET_TUNJANGAN_KENDARAAN' => 8,
            'DET_TUNJANGAN_LAIN' => 9,
            'FOTO_KTP' => null,
            'FOTO_SIM' => null,
            'FOTO_KK' => null,
            'FOTO_BPJS_KESEHATAN' => null,
            'FOTO_BPJS_KETENAGAKERJAAN' => null,
            'FOTO_KARYAWAN' => null,
            'FOTO_KONTRAK_KERJA' => null,
        ]);
    }
}