<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Vendor;
use App\Models\VendorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Vendor::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TGL_AWAL_JADI_VENDOR"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Vendor::first()->getAttributes(); // Get all attribute names
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
                'vendor_type' => [
                    'table' => 'vendor_types',
                    'column' => 'NAMA',
                ],
                'cityKTP' => [
                    'table' => 'cities',
                    'column' => 'NAMA',
                ],
                'cityKTP.province' => [
                    'table' => 'provinces',
                    'column' => 'NAMA',
                ],

                'cityKTP.province.country' => [
                    'table' => 'countries',
                    'column' => 'NAMA',
                ],

                'cityNPWP.province' => [
                    'table' => 'provinces',
                    'column' => 'NAMA',
                ],

                'cityNPWP.province.country' => [
                    'table' => 'countries',
                    'column' => 'NAMA',
                ],
                'cityNPWP' => [
                    'table' => 'cities',
                    'column' => 'NAMA',
                ],

            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $vendor) {
                $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
                $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
                $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;
            }
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return (int) substr($data->KODE, strpos($data->KODE, '.') + 1);
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
        $vendors = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($vendors as $vendor) {
                $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
                $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
                $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Vendor::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $vendors->values()->toArray(),
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
            4 => ['vendor_type', 'NAMA'],
            13 => ['cityKTP', 'NAMA'],
            14 => ['cityKTP.province', 'NAMA'],
            15 => ['cityKTP.province.country', 'NAMA'],
            29 => ['cityNPWP', 'NAMA'],
            30 => ['cityNPWP.province', 'NAMA'],
            31 => ['cityNPWP.province.country', 'NAMA'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Vendor::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = ["TGL_AWAL_JADI_VENDOR"]; // Add your date column names here

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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $vendor) {
                    $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
                    $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                    $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                    $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                    $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                    $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                    $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
                    $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                    $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                    $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                    $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                    $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                    $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;
                }
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return (int) substr($data->KODE, strpos($data->KODE, '.') + 1);
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

            $vendors = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($vendors as $vendor) {
                    $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
                    $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                    $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                    $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                    $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                    $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                    $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
                    $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                    $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                    $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                    $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                    $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                    $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Vendor::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $vendors->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $vendor = Vendor::where('KODE', $KODE)->first();
            if (!$vendor) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            //convert to array
            $vendor = $vendor->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $vendor);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function indexWeb()
    {
        try {
            $vendors = Vendor::orderBy('KODE', 'asc')->get();

            foreach ($vendors as $vendor) {
                $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
                $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
                $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $vendors);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }



    public function store(Request $request)
    {
        // Use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:vendors,NAMA',
            'KODE_JENIS_VENDOR' => 'required',
            'BADAN_HUKUM' => 'required',
            'STATUS' => 'required',
            'NO_KTP' => 'required',
            'NAMA_KTP' => 'required',
            'ALAMAT_KTP' => 'required',
            'RT_KTP' => 'required',
            'RW_KTP' => 'required',
            'KELURAHAN_KTP' => 'required',
            'KECAMATAN_KTP' => 'required',
            'KODE_KOTA_KTP' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'TELP_KANTOR' => 'required',
            'HP_KANTOR' => 'required',
            'WEBSITE' => 'required',
            'EMAIL' => 'required',
            'PLAFON' => 'required',
            'NO_NPWP' => 'required',
            'NAMA_NPWP' => 'required',
            'ALAMAT_NPWP' => 'required',
            'RT_NPWP' => 'required',
            'RW_NPWP' => 'required',
            'KELURAHAN_NPWP' => 'required',
            'KECAMATAN_NPWP' => 'required',
            'KODE_KOTA_NPWP' => 'required',
            'FOTO_NPWP' => 'mimes:jpeg,png',
            'CP' => 'required',
            'JABATAN_CP' => 'required',
            'NO_HP_CP' => 'required',
            'EMAIL_CP' => 'required',
            'NAMA_REKENING' => 'required',
            'NO_REKENING' => 'required',
            'NAMA_BANK' => 'required',
            'ALAMAT_BANK' => 'required',
            'TOP' => 'required',
            'KETERANGAN_TOP' => 'required',
            'TGL_AWAL_JADI_VENDOR' => 'required|date',
            'FORM_VENDOR' => 'mimes:doc,docx,pdf,jpeg,png,jpg',
            'PAYMENT' => 'required',
        ], []);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check if the client has input the same data
        $existingData = Vendor::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($existingData) {
            // $message = "Same data already exists with KODE " . $existingData->KODE . " and NAMA " . $existingData->NAMA . ". Do you still want to continue?";
            // // Data already exists, return a warning message
            // return ApiResponse::json(false, $message, null, 409);
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $foto_ktp = $request->file('FOTO_KTP');
            $foto_npwp = $request->file('FOTO_NPWP');
            $form_vendor = $request->file('FORM_VENDOR');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_npwp_name = null;
            $form_vendor_name = null;
            if ($foto_ktp) {
                $foto_ktp_name = $foto_ktp->getClientOriginalName();
                $foto_ktp_name = 'KTP' . '_' . uniqid() . '_' . $foto_ktp_name;
                $foto_ktp->storeAs('files', $foto_ktp_name);
            }
            if ($foto_npwp) {
                $foto_npwp_name = $foto_npwp->getClientOriginalName();
                $foto_npwp_name = 'NPWP' . '_' . uniqid() . '_' . $foto_npwp_name;
                $foto_npwp->storeAs('files', $foto_npwp_name);
            }

            if ($form_vendor) {
                $form_vendor_name = $form_vendor->getClientOriginalName();
                $form_vendor_name = 'FORM_VENDOR' . '_' . uniqid() . '_' . $form_vendor_name;
                $form_vendor->storeAs('files', $form_vendor_name);
            }

            $newVendor = new Vendor();
            $newVendor->KODE = $this->getLocalNextId();
            $newVendor->NAMA = $request->NAMA;
            $newVendor->KODE_JENIS_VENDOR = $request->KODE_JENIS_VENDOR;
            $newVendor->BADAN_HUKUM = $request->BADAN_HUKUM;
            $newVendor->STATUS = $request->STATUS;
            $newVendor->NO_KTP = $request->NO_KTP;
            $newVendor->NAMA_KTP = $request->NAMA_KTP;
            $newVendor->ALAMAT_KTP = $request->ALAMAT_KTP;
            $newVendor->RT_KTP = $request->RT_KTP;
            $newVendor->RW_KTP = $request->RW_KTP;
            $newVendor->KELURAHAN_KTP = $request->KELURAHAN_KTP;
            $newVendor->KECAMATAN_KTP = $request->KECAMATAN_KTP;
            $newVendor->KODE_KOTA_KTP = $request->KODE_KOTA_KTP;
            $newVendor->TELP_KANTOR = $request->TELP_KANTOR;
            $newVendor->HP_KANTOR = $request->HP_KANTOR;
            $newVendor->WEBSITE = $request->WEBSITE;
            $newVendor->EMAIL = $request->EMAIL;
            $newVendor->PLAFON = $request->PLAFON;
            $newVendor->NO_NPWP = $request->NO_NPWP;
            $newVendor->NAMA_NPWP = $request->NAMA_NPWP;
            $newVendor->ALAMAT_NPWP = $request->ALAMAT_NPWP;
            $newVendor->RT_NPWP = $request->RT_NPWP;
            $newVendor->RW_NPWP = $request->RW_NPWP;
            $newVendor->KELURAHAN_NPWP = $request->KELURAHAN_NPWP;
            $newVendor->KECAMATAN_NPWP = $request->KECAMATAN_NPWP;
            $newVendor->KODE_KOTA_NPWP = $request->KODE_KOTA_NPWP;
            $newVendor->CP = $request->CP;
            $newVendor->JABATAN_CP = $request->JABATAN_CP;
            $newVendor->NO_HP_CP = $request->NO_HP_CP;
            $newVendor->EMAIL_CP = $request->EMAIL_CP;
            $newVendor->NAMA_REKENING = $request->NAMA_REKENING;
            $newVendor->NO_REKENING = $request->NO_REKENING;
            $newVendor->NAMA_BANK = $request->NAMA_BANK;
            $newVendor->ALAMAT_BANK = $request->ALAMAT_BANK;
            $newVendor->TOP = $request->TOP;
            $newVendor->KETERANGAN_TOP = $request->KETERANGAN_TOP;
            $newVendor->TGL_AWAL_JADI_VENDOR = $request->TGL_AWAL_JADI_VENDOR;
            $newVendor->PAYMENT = $request->PAYMENT;



            //request get file
            if ($foto_ktp) {
                $newVendor->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_npwp) {
                $newVendor->FOTO_NPWP = $foto_npwp_name;
            }
            if ($form_vendor) {
                $newVendor->FORM_VENDOR = $form_vendor_name;
            }

            $newVendor->save();




            // check if save success, then return call the function to get customer group by kode
            if ($newVendor) {
                $newVendor = Vendor::where('KODE', $newVendor->KODE)->first();
                $city_KTP = City::where('KODE', $newVendor->KODE_KOTA_KTP)->first();
                $newVendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
                $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
                $newVendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
                $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
                $newVendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

                $city_NPWP = City::where('KODE', $newVendor->KODE_KOTA_NPWP)->first();
                $newVendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
                $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
                $newVendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
                $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
                $newVendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

                $newVendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $newVendor->KODE_JENIS_VENDOR)->first()->NAMA;
                //convert to array
                $newVendor = $newVendor->toArray();
                return ApiResponse::json(true, "Data inserted successfully with KODE $newVendor->KODE", $newVendor, 201);
            } else {
                return ApiResponse::json(false, "Data failed to insert", null, 500);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        // Use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:vendors,NAMA,' . $KODE . ',KODE',
            'KODE_JENIS_VENDOR' => 'required',
            'BADAN_HUKUM' => 'required',
            'STATUS' => 'required',
            'NO_KTP' => 'required',
            'NAMA_KTP' => 'required',
            'ALAMAT_KTP' => 'required',
            'RT_KTP' => 'required',
            'RW_KTP' => 'required',
            'KELURAHAN_KTP' => 'required',
            'KECAMATAN_KTP' => 'required',
            'KODE_KOTA_KTP' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'TELP_KANTOR' => 'required',
            'HP_KANTOR' => 'required',
            'WEBSITE' => 'required',
            'EMAIL' => 'required',
            'PLAFON' => 'required',
            'NO_NPWP' => 'required',
            'NAMA_NPWP' => 'required',
            'ALAMAT_NPWP' => 'required',
            'RT_NPWP' => 'required',
            'RW_NPWP' => 'required',
            'KELURAHAN_NPWP' => 'required',
            'KECAMATAN_NPWP' => 'required',
            'KODE_KOTA_NPWP' => 'required',
            'FOTO_NPWP' => 'mimes:jpeg,png',
            'CP' => 'required',
            'JABATAN_CP' => 'required',
            'NO_HP_CP' => 'required',
            'EMAIL_CP' => 'required',
            'NAMA_REKENING' => 'required',
            'NO_REKENING' => 'required',
            'NAMA_BANK' => 'required',
            'ALAMAT_BANK' => 'required',
            'TOP' => 'required',
            'KETERANGAN_TOP' => 'required',
            'TGL_AWAL_JADI_VENDOR' => 'required|date',
            'FORM_VENDOR' => 'mimes:doc,docx,pdf,jpeg,png,jpg',
            'PAYMENT' => 'required',
        ], []);

        // Check uniqueness for NAMA case insensitive except for current KODE
        $existingData = Vendor::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            // $message = "Same data already exists with KODE " . $existingData->KODE . " and NAMA " . $existingData->NAMA . ". Do you still want to continue?";
            // // Data already exists, return a warning message
            // return ApiResponse::json(false, $message, null, 409);
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }
        try {
            //request file if exist
            $foto_ktp = $request->file('FOTO_KTP');
            $foto_npwp = $request->file('FOTO_NPWP');
            $form_vendor = $request->file('FORM_VENDOR');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_npwp_name = null;
            $form_vendor_name = null;
            if ($foto_ktp) {
                $foto_ktp_name = $foto_ktp->getClientOriginalName();
                $foto_ktp_name = 'KTP' . '_' . uniqid() . '_' . $foto_ktp_name;
                $foto_ktp->storeAs('files', $foto_ktp_name);
            }
            if ($foto_npwp) {
                $foto_npwp_name = $foto_npwp->getClientOriginalName();
                $foto_npwp_name = 'NPWP' . '_' . uniqid() . '_' . $foto_npwp_name;
                $foto_npwp->storeAs('files', $foto_npwp_name);
            }

            if ($form_vendor) {
                $form_vendor_name = $form_vendor->getClientOriginalName();
                $form_vendor_name = 'FORM_VENDOR' . '_' . uniqid() . '_' . $form_vendor_name;
                $form_vendor->storeAs('files', $form_vendor_name);
            }






            $vendor = Vendor::findOrFail($KODE);
            if ($vendor->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update vendor because it has related records', null, 400);
            }
            $vendor->NAMA = $request->NAMA;
            $vendor->KODE_JENIS_VENDOR = $request->KODE_JENIS_VENDOR;
            $vendor->BADAN_HUKUM = $request->BADAN_HUKUM;
            $vendor->STATUS = $request->STATUS;
            $vendor->NO_KTP = $request->NO_KTP;
            $vendor->NAMA_KTP = $request->NAMA_KTP;
            $vendor->ALAMAT_KTP = $request->ALAMAT_KTP;
            $vendor->RT_KTP = $request->RT_KTP;
            $vendor->RW_KTP = $request->RW_KTP;
            $vendor->KELURAHAN_KTP = $request->KELURAHAN_KTP;
            $vendor->KECAMATAN_KTP = $request->KECAMATAN_KTP;
            $vendor->KODE_KOTA_KTP = $request->KODE_KOTA_KTP;
            $vendor->TELP_KANTOR = $request->TELP_KANTOR;
            $vendor->HP_KANTOR = $request->HP_KANTOR;
            $vendor->WEBSITE = $request->WEBSITE;
            $vendor->EMAIL = $request->EMAIL;
            $vendor->PLAFON = $request->PLAFON;
            $vendor->NO_NPWP = $request->NO_NPWP;
            $vendor->NAMA_NPWP = $request->NAMA_NPWP;
            $vendor->ALAMAT_NPWP = $request->ALAMAT_NPWP;
            $vendor->RT_NPWP = $request->RT_NPWP;
            $vendor->RW_NPWP = $request->RW_NPWP;
            $vendor->KELURAHAN_NPWP = $request->KELURAHAN_NPWP;
            $vendor->KECAMATAN_NPWP = $request->KECAMATAN_NPWP;
            $vendor->KODE_KOTA_NPWP = $request->KODE_KOTA_NPWP;
            $vendor->CP = $request->CP;
            $vendor->JABATAN_CP = $request->JABATAN_CP;
            $vendor->NO_HP_CP = $request->NO_HP_CP;
            $vendor->EMAIL_CP = $request->EMAIL_CP;
            $vendor->NAMA_REKENING = $request->NAMA_REKENING;
            $vendor->NO_REKENING = $request->NO_REKENING;
            $vendor->NAMA_BANK = $request->NAMA_BANK;
            $vendor->ALAMAT_BANK = $request->ALAMAT_BANK;
            $vendor->TOP = $request->TOP;
            $vendor->KETERANGAN_TOP = $request->KETERANGAN_TOP;
            $vendor->TGL_AWAL_JADI_VENDOR = $request->TGL_AWAL_JADI_VENDOR;
            $vendor->PAYMENT = $request->PAYMENT;



            //request get file
            if ($foto_ktp) {
                $vendor->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_npwp) {
                $vendor->FOTO_NPWP = $foto_npwp_name;
            }
            if ($form_vendor) {
                $vendor->FORM_VENDOR = $form_vendor_name;
            }

            $vendor->save();

            if (!$vendor) {
                return ApiResponse::json(false, 'Failed to update customer', null, 500);
            }


            $vendor = Vendor::where('KODE', $KODE)->first();


            $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
            $vendor->NAMA_KOTA_KTP = $city_KTP->NAMA;
            $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
            $vendor->NAMA_PROVINSI_KTP = $province_KTP->NAMA;
            $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
            $vendor->NAMA_NEGARA_KTP = $country_KTP->NAMA;

            $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
            $vendor->NAMA_KOTA_NPWP = $city_NPWP->NAMA;
            $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
            $vendor->NAMA_PROVINSI_NPWP = $province_NPWP->NAMA;
            $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
            $vendor->NAMA_NEGARA_NPWP = $country_NPWP->NAMA;

            $vendor->NAMA_JENIS_VENDOR = VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA;

            //convert to array
            $vendor = $vendor->toArray();

            return ApiResponse::json(true, 'Customer successfully updated', $vendor);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }



    public function destroy($KODE)
    {
        try {
            $vendor = Vendor::findOrFail($KODE);
            $vendor->delete();
            return ApiResponse::json(true, 'Vendor successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $vendors = Vendor::onlyTrashed()->get();

        foreach ($vendors as $vendor) {
            $city_KTP = City::where('KODE', $vendor->KODE_KOTA_KTP)->first();
            $vendor->setAttribute('NAMA_KOTA_KTP', $city_KTP->NAMA);
            $province_KTP = Province::where('KODE', $city_KTP->KODE_PROVINSI)->first();
            $vendor->setAttribute('NAMA_PROVINSI_KTP', $province_KTP->NAMA);
            $country_KTP = Country::where('KODE', $province_KTP->KODE_NEGARA)->first();
            $vendor->setAttribute('NAMA_NEGARA_KTP', $country_KTP->NAMA);

            $city_NPWP = City::where('KODE', $vendor->KODE_KOTA_NPWP)->first();
            $vendor->setAttribute('NAMA_KOTA_NPWP', $city_NPWP->NAMA);
            $province_NPWP = Province::where('KODE', $city_NPWP->KODE_PROVINSI)->first();
            $vendor->setAttribute('NAMA_PROVINSI_NPWP', $province_NPWP->NAMA);
            $country_NPWP = Country::where('KODE', $province_NPWP->KODE_NEGARA)->first();
            $vendor->setAttribute('NAMA_NEGARA_NPWP', $country_NPWP->NAMA);
            $vendor->setAttribute('NAMA_JENIS_VENDOR', VendorType::where('KODE', $vendor->KODE_JENIS_VENDOR)->first()->NAMA);
        }

        return ApiResponse::json(true, 'Trash bin fetched', $vendors);
    }



    public function restore($id)
    {
        $restored = Vendor::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        $nextId = $this->getLocalNextId();

        return ApiResponse::json(true, 'Next KODE retrieved successfully', $nextId);
    }

    public function getLocalNextId()
    {
        // Get the maximum COM.X ID from the database
        $maxId = Vendor::where('KODE', 'LIKE', 'VDR.%')
            ->withTrashed()
            ->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) DESC, "KODE" DESC')
            ->value('KODE');

        if ($maxId) {
            // Extract the number from the maximum ID
            $maxNumber = (int) substr($maxId, strpos($maxId, '.') + 1);

            // Increment the number
            $nextNumber = $maxNumber + 1;
        } else {
            // If no existing IDs found, start with 1
            $nextNumber = 1;
        }

        // Create the next ID by concatenating 'COM.' with the incremented number
        $nextId = 'VDR.' . $nextNumber;

        return $nextId;
    }
}