<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Position;
use App\Models\Staff;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Staff::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TTL", "TGL_MULAI_KERJA", "TGL_SELESAI_KONTRAK"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Staff::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes, $dateColumns) {
                foreach ($attributes as $attribute => $value) {

                    if (in_array($attribute, $dateColumns)) {
                        // Modify the attribute value if it's a date column
                        $q->orWhere(DB::raw("TO_CHAR(\"$attribute\", 'dd-mm-yyyy')"), 'LIKE', "%$searchValue%");
                    } else {
                        $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                    }
                }
            });

            $relatedColumns = [
                'positions' => [
                    'table' => 'positions',
                    'column' => 'NAMA',
                ],
                'location' => [
                    'table' => 'warehouses',
                    'column' => 'NAMA',
                ],

            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        $query->orderBy('KODE', 'asc');

        //order by kode integer ascending


        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;
            }
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return (int) $data->KODE;
                }, SORT_REGULAR, $sortBoolean);
            } else {
                $allData = $allData->sortBy($columnToSort, SORT_REGULAR, $sortBoolean);
            }
        }



        // Apply pagination and limit
        $limit = $request->get('length', 10);
        $total_data_after_search = $query->count();
        if ($limit == -1) {
            $limit = $total_data_after_search; // Use the total count after search filters
        }
        $page = $request->get('start', 0) / $limit + 1;
        $skip = ($page - 1) * $limit;

        // $customers = array_slice($allDataArray, $skip, $limit);
        $Users = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($Users as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Staff::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $Users->values()->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [
            5 => ['positions', 'NAMA'],
            6 => ['location', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Staff::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = ["TTL", "TGL_MULAI_KERJA", "TGL_SELESAI_KONTRAK"]; // Add your date column names here


            if ($hasSearch) {
                foreach ($columns as $index => $column) {
                    if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                        if ($column['searchable'] === 'true') {
                            $searchValue = strtoupper($column['search']['value']);
                            // Remove parentheses from search value
                            $searchValue = str_replace(['(', ')'], '', $searchValue);

                            if (array_key_exists($index, $relationColumnsAndTo)) {
                                [$relation, $relatedColumn] = $relationColumnsAndTo[$index];

                                $query->whereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                                    $q->where($relatedColumn, 'LIKE', "%$searchValue%");
                                });
                            } else {
                                $columnName = $columns[$index]['data'];
                                if (in_array($columnName, $dateColumns)) {
                                    // Modify the attribute value if it's a date column
                                    $query->where(DB::raw("TO_CHAR(\"$columnName\", 'dd-mm-yyyy')"), 'LIKE', "%$searchValue%");
                                } else {
                                    $query->where($columnName, 'LIKE', "%$searchValue%");
                                }
                            }
                        }
                    }
                }
            }

            $query->orderBy('KODE', 'asc');


            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $User) {
                    $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                    $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;
                }
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return (int) $data->KODE;
                    }, SORT_REGULAR, $sortBoolean);
                } else {
                    $allData = $allData->sortBy($columnToSort, SORT_REGULAR, $sortBoolean);
                }
            }

            // Convert the collection of cities to an array

            // Apply pagination and limit
            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;

            $Users = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($Users as $User) {
                    $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                    $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Staff::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $Users->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        //return string kode
        try {
            $User = Staff::where('KODE', $KODE)->first();
            if (!$User) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            //convert to array
            $User = $User->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function findByKodeJabatan($KODE)
    {
        try {
            $User = Staff::where('KODE_JABATAN', $KODE)->get();
            if (!$User) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function index()
    {
        try {
            $Users = Staff::all();
            foreach ($Users as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully', $Users);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required',
            'EMAIL' => 'required',
            'NICKNAME' => 'required',
            'KODE_JABATAN' => 'required',
            'KODE_LOKASI' => 'required',
            'NO_HP' => 'required',
            'NO_HP_KANTOR' => 'required',
            'NO_HP_KELUARGA' => 'required',
            'KETERANGAN_KELUARGA' => 'required',
            'NIK' => 'required|unique:staffs,NIK',
            'NO_SIM' => 'required|unique:staffs,NO_SIM',
            'ALAMAT_KTP' => 'required',
            'ALAMAT_DOMISILI' => 'required',
            'TTL' => 'required',
            'JENIS_KELAMIN' => 'required',
            'AGAMA' => 'required',
            'STATUS_PERNIKAHAN' => 'required',
            'JUMLAH_ANAK' => 'required',
            'TGL_MULAI_KERJA' => 'required',
            'TGL_SELESAI_KONTRAK' => 'required',
            'STATUS_KARYAWAN' => 'required',
            'JAM_MASUK' => 'required',
            'JAM_KELUAR' => 'required',
            'ACCOUNT_NUMBER' => 'required',
            'BANK' => 'required',
            'ATAS_NAMA' => 'required',
            'GAJI_POKOK' => 'required',
            'DET_GAJI_POKOK' => 'required',
            'BPJS_KESEHATAN' => 'required',
            'DET_BPJS_KESEHATAN' => '',
            'BPJS_KETENAGAKERJAAN' => 'required',
            'DET_BPJS_KETENAGAKERJAAN' => '',
            'UANG_MAKAN'    => 'required',
            'DET_UANG_MAKAN' => '',
            'UANG_TRANSPORT' => 'required',
            'DET_UANG_TRANSPORT' => '',
            'UANG_LEMBUR' => 'required',
            'DET_UANG_LEMBUR' => '',
            'PULSA' => 'required',
            'DET_PULSA' => '',
            'TUNJANGAN_KENDARAAN' => 'required',
            'DET_TUNJANGAN_KENDARAAN' => '',
            'TUNJANGAN_LAIN' => 'required',
            'DET_TUNJANGAN_LAIN'    => '',
            'KETERANGAN_TUNJANGAN_LAIN' => '',

            'INSENTIF' => 'required',
            'THR' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'FOTO_KK' => 'mimes:jpeg,png',
            'FOTO_SIM' => 'mimes:jpeg,png',
            'FOTO_BPJS_KESEHATAN' => 'mimes:jpeg,png',
            'FOTO_BPJS_KETENAGAKERJAAN' => 'mimes:jpeg,png',
            'FOTO_KARYAWAN' => 'mimes:jpeg,png',
            'FOTO_KONTRAK_KERJA' => 'mimes:jpeg,png',

        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = Staff::where(DB::raw('LOWER("EMAIL")'), strtolower($request->EMAIL))->first();
        if ($existingData) {

            return ApiResponse::json(false, ['EMAIL' => ['The EMAIL has already been taken.']], null, 422);
        }
        try {
            $foto_ktp = $request->file('FOTO_KTP');
            $foto_sim = $request->file('FOTO_SIM');
            $foto_kk = $request->file('FOTO_KK');
            $foto_bpjs_kesehatan = $request->file('FOTO_BPJS_KESEHATAN');
            $foto_bpjs_ketenagakerjaan = $request->file('FOTO_BPJS_KETENAGAKERJAAN');
            $foto_karyawan = $request->file('FOTO_KARYAWAN');
            $foto_kontrak_kerja = $request->file('FOTO_KONTRAK_KERJA');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_sim_name = null;
            $foto_kk_name = null;
            $foto_bpjs_kesehatan_name = null;
            $foto_bpjs_ketenagakerjaan_name = null;
            $foto_karyawan_name = null;
            $foto_kontrak_kerja_name = null;

            if ($foto_ktp) {
                $foto_ktp_name = $foto_ktp->getClientOriginalName();
                $foto_ktp_name = 'KTP' . '_' . uniqid() . '_' . $foto_ktp_name;
                $foto_ktp->storeAs('files', $foto_ktp_name);
            }
            if ($foto_sim) {
                $foto_sim_name = $foto_sim->getClientOriginalName();
                $foto_sim_name = 'SIM' . '_' . uniqid() . '_' . $foto_sim_name;
                $foto_sim->storeAs('files', $foto_sim_name);
            }
            if ($foto_kk) {
                $foto_kk_name = $foto_kk->getClientOriginalName();
                $foto_kk_name = 'KK' . '_' . uniqid() . '_' . $foto_kk_name;
                $foto_kk->storeAs('files', $foto_kk_name);
            }
            if ($foto_bpjs_kesehatan) {
                $foto_bpjs_kesehatan_name = $foto_bpjs_kesehatan->getClientOriginalName();
                $foto_bpjs_kesehatan_name = 'BPJS_KESEHATAN' . '_' . uniqid() . '_' . $foto_bpjs_kesehatan_name;
                $foto_bpjs_kesehatan->storeAs('files', $foto_bpjs_kesehatan_name);
            }
            if ($foto_bpjs_ketenagakerjaan) {
                $foto_bpjs_ketenagakerjaan_name = $foto_bpjs_ketenagakerjaan->getClientOriginalName();
                $foto_bpjs_ketenagakerjaan_name = 'BPJS_KETENAGAKERJAAN' . '_' . uniqid() . '_' . $foto_bpjs_ketenagakerjaan_name;
                $foto_bpjs_ketenagakerjaan->storeAs('files', $foto_bpjs_ketenagakerjaan_name);
            }
            if ($foto_karyawan) {
                $foto_karyawan_name = $foto_karyawan->getClientOriginalName();
                $foto_karyawan_name = 'KARYAWAN' . '_' . uniqid() . '_' . $foto_karyawan_name;
                $foto_karyawan->storeAs('files', $foto_karyawan_name);
            }
            if ($foto_kontrak_kerja) {
                $foto_kontrak_kerja_name = $foto_kontrak_kerja->getClientOriginalName();
                $foto_kontrak_kerja_name = 'KONTRAK_KERJA' . '_' . uniqid() . '_' . $foto_kontrak_kerja_name;
                $foto_kontrak_kerja->storeAs('files', $foto_kontrak_kerja_name);
            }


            $newUser = new Staff();
            $newUser->KODE = Staff::withTrashed()->max('KODE') + 1;
            $newUser->NAMA = $request->get('NAMA');
            $newUser->EMAIL = $request->get('EMAIL');

            $newUser->NICKNAME = $request->get('NICKNAME');
            $newUser->KODE_JABATAN = $request->get('KODE_JABATAN');
            //  'KODE_LOKASI' => 'required',
            $newUser->KODE_LOKASI = $request->get('KODE_LOKASI');
            //no hp
            $newUser->NO_HP = $request->get('NO_HP');
            //no hp kantor
            $newUser->NO_HP_KANTOR = $request->get('NO_HP_KANTOR');
            //no hp keluarga
            $newUser->NO_HP_KELUARGA = $request->get('NO_HP_KELUARGA');

            // KETERANGAN_KELUARGA
            $newUser->KETERANGAN_KELUARGA = $request->get('KETERANGAN_KELUARGA');
            //NIK
            $newUser->NIK = $request->get('NIK');
            //SIM
            $newUser->NO_SIM = $request->get('NO_SIM');
            //alamat ktp
            $newUser->ALAMAT_KTP = $request->get('ALAMAT_KTP');
            //alamat domisili
            $newUser->ALAMAT_DOMISILI = $request->get('ALAMAT_DOMISILI');
            //ttl
            $newUser->TTL = $request->get('TTL');
            //jenis kelamin
            $newUser->JENIS_KELAMIN = $request->get('JENIS_KELAMIN');
            //agama
            $newUser->AGAMA = $request->get('AGAMA');
            //status pernikahan
            $newUser->STATUS_PERNIKAHAN = $request->get('STATUS_PERNIKAHAN');
            //jumlah anak
            $newUser->JUMLAH_ANAK = $request->get('JUMLAH_ANAK');
            //tgl muulai kerja
            $newUser->TGL_MULAI_KERJA = $request->get('TGL_MULAI_KERJA');
            //tgl selesai kontrak
            $newUser->TGL_SELESAI_KONTRAK = $request->get('TGL_SELESAI_KONTRAK');
            //status karyawan
            $newUser->STATUS_KARYAWAN = $request->get('STATUS_KARYAWAN');
            //jam masuk
            $newUser->JAM_MASUK = $request->get('JAM_MASUK');
            //jam keluar
            $newUser->JAM_KELUAR = $request->get('JAM_KELUAR');
            //account nuumber
            $newUser->ACCOUNT_NUMBER = $request->get('ACCOUNT_NUMBER');
            //bank
            $newUser->BANK = $request->get('BANK');
            //atas nama
            $newUser->ATAS_NAMA = $request->get('ATAS_NAMA');
            //gaji pokok
            $newUser->GAJI_POKOK = $request->get('GAJI_POKOK');
            //det gaji pokok
            $newUser->DET_GAJI_POKOK = $request->get('DET_GAJI_POKOK');
            //bpjs kesehatan
            $newUser->BPJS_KESEHATAN = $request->get('BPJS_KESEHATAN');
            //det bpjs kesehatan
            $newUser->DET_BPJS_KESEHATAN = $request->get('DET_BPJS_KESEHATAN');
            //bps ketenagakerjaan
            $newUser->BPJS_KETENAGAKERJAAN = $request->get('BPJS_KETENAGAKERJAAN');
            //det bpjs ketenagakerjaan
            $newUser->DET_BPJS_KETENAGAKERJAAN = $request->get('DET_BPJS_KETENAGAKERJAAN');
            //uang makan
            $newUser->UANG_MAKAN = $request->get('UANG_MAKAN');
            //det uang makan
            $newUser->DET_UANG_MAKAN = $request->get('DET_UANG_MAKAN');
            //uang transport
            $newUser->UANG_TRANSPORT = $request->get('UANG_TRANSPORT');
            //det uang transport
            $newUser->DET_UANG_TRANSPORT = $request->get('DET_UANG_TRANSPORT');
            //uang lembur
            $newUser->UANG_LEMBUR = $request->get('UANG_LEMBUR');
            //det uang lembur
            $newUser->DET_UANG_LEMBUR = $request->get('DET_UANG_LEMBUR');
            //pulsa
            $newUser->PULSA = $request->get('PULSA');
            //det pulsa
            $newUser->DET_PULSA = $request->get('DET_PULSA');
            //tunjangan kendaraan
            $newUser->TUNJANGAN_KENDARAAN = $request->get('TUNJANGAN_KENDARAAN');
            //det tunjangan kendaraan
            $newUser->DET_TUNJANGAN_KENDARAAN = $request->get('DET_TUNJANGAN_KENDARAAN');
            //tunjangan lain
            $newUser->TUNJANGAN_LAIN = $request->get('TUNJANGAN_LAIN');
            //det tunjangan lain
            $newUser->DET_TUNJANGAN_LAIN = $request->get('DET_TUNJANGAN_LAIN');
            //keterangan tunjangan lain
            $newUser->KETERANGAN_TUNJANGAN_LAIN = $request->get('KETERANGAN_TUNJANGAN_LAIN');
            //insentif
            $newUser->INSENTIF = $request->get('INSENTIF');
            //THR
            $newUser->THR = $request->get('THR');


            //request get file
            if ($foto_ktp) {
                $newUser->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_sim) {
                $newUser->FOTO_SIM = $foto_sim_name;
            }
            if ($foto_kk) {
                $newUser->FOTO_KK = $foto_kk_name;
            }
            if ($foto_bpjs_kesehatan) {
                $newUser->FOTO_BPJS_KESEHATAN = $foto_bpjs_kesehatan_name;
            }
            if ($foto_bpjs_ketenagakerjaan) {
                $newUser->FOTO_BPJS_KETENAGAKERJAAN = $foto_bpjs_ketenagakerjaan_name;
            }
            if ($foto_karyawan) {
                $newUser->FOTO_KARYAWAN = $foto_karyawan_name;
            }
            if ($foto_kontrak_kerja) {
                $newUser->FOTO_KONTRAK_KERJA = $foto_kontrak_kerja_name;
            }


            $newUser->save();


            if ($newUser) {
                //get nama jabatan
                $nama_jabatan = Position::where('KODE', $request->get('KODE_JABATAN'))->first()->NAMA;
                // create associative array
                $resp_User = Staff::where('KODE', $newUser->KODE)->first()->toArray();
                $resp_User['NAMA_JABATAN'] = $nama_jabatan;

                // GET  LOKASI
                $lokasi = Warehouse::where('KODE', $request->get('KODE_LOKASI'))->first();
                $resp_User['NAMA_LOKASI'] = $lokasi->NAMA;


                return ApiResponse::json(true, "Data inserted successfully with KODE $newUser->KODE", $resp_User);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required',
            'EMAIL' => 'required',
            'NICKNAME' => 'required',
            'KODE_JABATAN' => 'required',
            'KODE_LOKASI' => 'required',
            'NO_HP' => 'required',
            'NO_HP_KANTOR' => 'required',
            'NO_HP_KELUARGA' => 'required',
            'KETERANGAN_KELUARGA' => 'required',
            'NIK' => 'required|unique:staffs,NIK,' . $KODE . ',KODE',
            'NO_SIM' => 'required|unique:staffs,NO_SIM,' . $KODE . ',KODE',
            'ALAMAT_KTP' => 'required',
            'ALAMAT_DOMISILI' => 'required',
            'TTL' => 'required',
            'JENIS_KELAMIN' => 'required',
            'AGAMA' => 'required',
            'STATUS_PERNIKAHAN' => 'required',
            'JUMLAH_ANAK' => 'required',
            'TGL_MULAI_KERJA' => 'required',
            'TGL_SELESAI_KONTRAK' => 'required',
            'STATUS_KARYAWAN' => 'required',
            'JAM_MASUK' => 'required',
            'JAM_KELUAR' => 'required',
            'ACCOUNT_NUMBER' => 'required',
            'BANK' => 'required',
            'ATAS_NAMA' => 'required',
            'GAJI_POKOK' => 'required',
            'DET_GAJI_POKOK' => 'required',
            'BPJS_KESEHATAN' => 'required',
            'DET_BPJS_KESEHATAN' => '',
            'BPJS_KETENAGAKERJAAN' => 'required',
            'DET_BPJS_KETENAGAKERJAAN' => '',
            'UANG_MAKAN'    => 'required',
            'DET_UANG_MAKAN' => '',
            'UANG_TRANSPORT' => 'required',
            'DET_UANG_TRANSPORT' => '',
            'UANG_LEMBUR' => 'required',
            'DET_UANG_LEMBUR' => '',
            'PULSA' => 'required',
            'DET_PULSA' => '',
            'TUNJANGAN_KENDARAAN' => 'required',
            'DET_TUNJANGAN_KENDARAAN' => '',
            'TUNJANGAN_LAIN' => 'required',
            'DET_TUNJANGAN_LAIN'    => '',
            'KETERANGAN_TUNJANGAN_LAIN' => '',
            'INSENTIF' => 'required',
            'THR' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'FOTO_SIM' => 'mimes:jpeg,png',
            'FOTO_KK' => 'mimes:jpeg,png',
            'FOTO_BPJS_KESEHATAN' => 'mimes:jpeg,png',
            'FOTO_BPJS_KETENAGAKERJAAN' => 'mimes:jpeg,png',
            'FOTO_KARYAWAN' => 'mimes:jpeg,png',
            'FOTO_KONTRAK_KERJA' => 'mimes:jpeg,png',

        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = Staff::where(DB::raw('LOWER("EMAIL")'), strtolower($request->EMAIL))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {

            return ApiResponse::json(false, ['EMAIL' => ['The EMAIL has already been taken.']], null, 422);
        }
        try {



            $foto_ktp = $request->file('FOTO_KTP');
            $foto_sim = $request->file('FOTO_SIM');
            $foto_kk = $request->file('FOTO_KK');
            $foto_bpjs_kesehatan = $request->file('FOTO_BPJS_KESEHATAN');
            $foto_bpjs_ketenagakerjaan = $request->file('FOTO_BPJS_KETENAGAKERJAAN');
            $foto_karyawan = $request->file('FOTO_KARYAWAN');
            $foto_kontrak_kerja = $request->file('FOTO_KONTRAK_KERJA');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_sim_name = null;
            $foto_kk_name = null;
            $foto_bpjs_kesehatan_name = null;
            $foto_bpjs_ketenagakerjaan_name = null;
            $foto_karyawan_name = null;
            $foto_kontrak_kerja_name = null;

            if ($foto_ktp) {
                $foto_ktp_name = $foto_ktp->getClientOriginalName();
                $foto_ktp_name = 'KTP' . '_' . uniqid() . '_' . $foto_ktp_name;
                $foto_ktp->storeAs('files', $foto_ktp_name);
            }
            if ($foto_sim) {
                $foto_sim_name = $foto_sim->getClientOriginalName();
                $foto_sim_name = 'SIM' . '_' . uniqid() . '_' . $foto_sim_name;
                $foto_sim->storeAs('files', $foto_sim_name);
            }
            if ($foto_kk) {
                $foto_kk_name = $foto_kk->getClientOriginalName();
                $foto_kk_name = 'KK' . '_' . uniqid() . '_' . $foto_kk_name;
                $foto_kk->storeAs('files', $foto_kk_name);
            }
            if ($foto_bpjs_kesehatan) {
                $foto_bpjs_kesehatan_name = $foto_bpjs_kesehatan->getClientOriginalName();
                $foto_bpjs_kesehatan_name = 'BPJS_KESEHATAN' . '_' . uniqid() . '_' . $foto_bpjs_kesehatan_name;
                $foto_bpjs_kesehatan->storeAs('files', $foto_bpjs_kesehatan_name);
            }
            if ($foto_bpjs_ketenagakerjaan) {
                $foto_bpjs_ketenagakerjaan_name = $foto_bpjs_ketenagakerjaan->getClientOriginalName();
                $foto_bpjs_ketenagakerjaan_name = 'BPJS_KETENAGAKERJAAN' . '_' . uniqid() . '_' . $foto_bpjs_ketenagakerjaan_name;
                $foto_bpjs_ketenagakerjaan->storeAs('files', $foto_bpjs_ketenagakerjaan_name);
            }
            if ($foto_karyawan) {
                $foto_karyawan_name = $foto_karyawan->getClientOriginalName();
                $foto_karyawan_name = 'KARYAWAN' . '_' . uniqid() . '_' . $foto_karyawan_name;
                $foto_karyawan->storeAs('files', $foto_karyawan_name);
            }
            if ($foto_kontrak_kerja) {
                $foto_kontrak_kerja_name = $foto_kontrak_kerja->getClientOriginalName();
                $foto_kontrak_kerja_name = 'KONTRAK_KERJA' . '_' . uniqid() . '_' . $foto_kontrak_kerja_name;
                $foto_kontrak_kerja->storeAs('files', $foto_kontrak_kerja_name);
            }







            $User = Staff::findOrFail($KODE);
            $User->NAMA = $request->get('NAMA');
            $User->EMAIL = $request->get('EMAIL');

            $User->NICKNAME = $request->get('NICKNAME');
            $User->KODE_JABATAN = $request->get('KODE_JABATAN');
            // lokasi
            $User->KODE_LOKASI = $request->get('KODE_LOKASI');
            //no hp
            $User->NO_HP = $request->get('NO_HP');
            //no hp kantor
            $User->NO_HP_KANTOR = $request->get('NO_HP_KANTOR');
            //no hp keluarga
            $User->NO_HP_KELUARGA = $request->get('NO_HP_KELUARGA');

            //KETERANGAN_KELUARGA
            $User->KETERANGAN_KELUARGA = $request->get('KETERANGAN_KELUARGA');

            //NIK
            $User->NIK = $request->get('NIK');
            //SIM
            $User->NO_SIM = $request->get('NO_SIM');
            //alamat ktp
            $User->ALAMAT_KTP = $request->get('ALAMAT_KTP');
            //alamat domisili
            $User->ALAMAT_DOMISILI = $request->get('ALAMAT_DOMISILI');
            //ttl
            $User->TTL = $request->get('TTL');
            //jenis kelamin
            $User->JENIS_KELAMIN = $request->get('JENIS_KELAMIN');
            //agama
            $User->AGAMA = $request->get('AGAMA');
            //status pernikahan
            $User->STATUS_PERNIKAHAN = $request->get('STATUS_PERNIKAHAN');
            //jumlah anak
            $User->JUMLAH_ANAK = $request->get('JUMLAH_ANAK');
            //tgl muulai kerja
            $User->TGL_MULAI_KERJA = $request->get('TGL_MULAI_KERJA');
            //tgl selesai kontrak
            $User->TGL_SELESAI_KONTRAK = $request->get('TGL_SELESAI_KONTRAK');
            //status karyawan
            $User->STATUS_KARYAWAN = $request->get('STATUS_KARYAWAN');
            //jam masuk
            $User->JAM_MASUK = $request->get('JAM_MASUK');
            //jam keluar
            $User->JAM_KELUAR = $request->get('JAM_KELUAR');
            //account nuumber
            $User->ACCOUNT_NUMBER = $request->get('ACCOUNT_NUMBER');
            //bank
            $User->BANK = $request->get('BANK');
            //atas nama
            $User->ATAS_NAMA = $request->get('ATAS_NAMA');
            //gaji pokok
            $User->GAJI_POKOK = $request->get('GAJI_POKOK');
            //det gaji pokok
            $User->DET_GAJI_POKOK = $request->get('DET_GAJI_POKOK');
            //bpjs kesehatan
            $User->BPJS_KESEHATAN = $request->get('BPJS_KESEHATAN');
            //det bpjs kesehatan
            $User->DET_BPJS_KESEHATAN = $request->get('DET_BPJS_KESEHATAN');
            //bps ketenagakerjaan
            $User->BPJS_KETENAGAKERJAAN = $request->get('BPJS_KETENAGAKERJAAN');
            //det bpjs ketenagakerjaan
            $User->DET_BPJS_KETENAGAKERJAAN = $request->get('DET_BPJS_KETENAGAKERJAAN');
            //uang makan
            $User->UANG_MAKAN = $request->get('UANG_MAKAN');
            //det uang makan
            $User->DET_UANG_MAKAN = $request->get('DET_UANG_MAKAN');
            //uang transport
            $User->UANG_TRANSPORT = $request->get('UANG_TRANSPORT');
            //det uang transport
            $User->DET_UANG_TRANSPORT = $request->get('DET_UANG_TRANSPORT');
            //uang lembur
            $User->UANG_LEMBUR = $request->get('UANG_LEMBUR');
            //det uang lembur
            $User->DET_UANG_LEMBUR = $request->get('DET_UANG_LEMBUR');
            //pulsa
            $User->PULSA = $request->get('PULSA');
            //det pulsa
            $User->DET_PULSA = $request->get('DET_PULSA');
            //tunjangan kendaraan
            $User->TUNJANGAN_KENDARAAN = $request->get('TUNJANGAN_KENDARAAN');
            //det tunjangan kendaraan
            $User->DET_TUNJANGAN_KENDARAAN = $request->get('DET_TUNJANGAN_KENDARAAN');
            //tunjangan lain
            $User->TUNJANGAN_LAIN = $request->get('TUNJANGAN_LAIN');
            //det tunjangan lain
            $User->DET_TUNJANGAN_LAIN = $request->get('DET_TUNJANGAN_LAIN');

            $User->KETERANGAN_TUNJANGAN_LAIN = $request->get('KETERANGAN_TUNJANGAN_LAIN');

            //insentif
            $User->INSENTIF = $request->get('INSENTIF');
            //THR
            $User->THR = $request->get('THR');


            //request get file
            if ($foto_ktp) {
                $User->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_sim) {
                $User->FOTO_SIM = $foto_sim_name;
            }
            if ($foto_kk) {
                $User->FOTO_KK = $foto_kk_name;
            }
            if ($foto_bpjs_kesehatan) {
                $User->FOTO_BPJS_KESEHATAN = $foto_bpjs_kesehatan_name;
            }
            if ($foto_bpjs_ketenagakerjaan) {
                $User->FOTO_BPJS_KETENAGAKERJAAN = $foto_bpjs_ketenagakerjaan_name;
            }
            if ($foto_karyawan) {
                $User->FOTO_KARYAWAN = $foto_karyawan_name;
            }
            if ($foto_kontrak_kerja) {
                $User->FOTO_KONTRAK_KERJA = $foto_kontrak_kerja_name;
            }


            $User->save();

            if (!$User) {
                return ApiResponse::json(false, 'Failed to update User', null, 500);
            }

            $User = Staff::where('KODE', $KODE)->first();

            $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
            $User->NAMA_LOKASI = Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA;

            //convert to array
            $User = $User->toArray();

            return ApiResponse::json(true, 'Staff successfully updated', $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $User = Staff::findOrFail($KODE);
            $User->delete();
            return ApiResponse::json(true, 'Staff successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete User', null, 500);
        }
    }



    public function trash()
    {
        $Users = Staff::onlyTrashed()->get();

        foreach ($Users as $User) {
            // get nama jabatan
            $User->setAttribute('NAMA_JABATAN', Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA);
            $User->setAttribute('NAMA_LOKASI', Warehouse::where('KODE', $User->KODE_LOKASI)->first()->NAMA);
        }

        return ApiResponse::json(true, 'Trash bin fetched', $Users);
    }



    public function restore($id)
    {
        $restored = Staff::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        // $tableName = 'countries'; // Replace with your table name
        // $autoIncrementColumn = 'KODE'; // Replace with your auto increment column name
        // $sequenceName = $tableName . '_' . $autoIncrementColumn . '_seq';

        // $query = DB::raw("SELECT nextval('\"$sequenceName\"') AS next_id");

        // $result = DB::select($query);

        // $nextId = $result[0]->next_id;

        // return response()->json(['next_id' => $nextId]);

        //get max KODE
        $maxKODE = Staff::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
