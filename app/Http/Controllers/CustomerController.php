<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\BusinessType;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Province;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{


    public function dropdown()
    {

        //get all city
        $cities = City::all();
        $cities = $cities->toArray();
        //get all customer group
        $customerGroups = CustomerGroup::all();
        $customerGroups = $customerGroups->toArray();
        //get all business type
        $businessTypes = BusinessType::all();
        $businessTypes = $businessTypes->toArray();
        //get staff where KODE_JABATAN = JBT.6, JBT.7, JBT.16
        $salesStaffs = Staff::where('KODE_JABATAN', 'JBT.6')->orWhere('KODE_JABATAN', 'JBT.7')->orWhere('KODE_JABATAN', 'JBT.16')->get();
        $salesStaffs = $salesStaffs->toArray();
        //get staff where KODE_JABATAN = JBT.3
        $arStaffs = Staff::where('KODE_JABATAN', 'JBT.3')->get();
        $arStaffs = $arStaffs->toArray();
        //return all data
        return ApiResponse::json(true, null, [

            'cities' => $cities,
            'customerGroups' => $customerGroups,
            'businessTypes' => $businessTypes,
            'salesStaffs' => $salesStaffs,
            'arStaffs' => $arStaffs,
        ]);
    }



    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Customer::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = ["TGL_REG"]; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Customer::first()->getAttributes(); // Get all attribute names
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
                'customerGroup' => [
                    'table' => 'customer_groups',
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
                'businessType' => [
                    'table' => 'business_types',
                    'column' => 'NAMA',
                ],
                'salesStaff' => [
                    'table' => 'staffs',
                    'column' => 'NAMA',
                ],
                'arStaff' => [
                    'table' => 'staffs',
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
            foreach ($allData as $customer) {

                $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
                //get kota name
                $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
                $city_KTP_nama = $city_KTP->NAMA;
                $customer->NAMA_KOTA_KTP = $city_KTP_nama;
                $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                $province_KTP_nama = $province_KTP->NAMA;
                $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
                $country_KTP_kode = $province_KTP->KODE_NEGARA;
                $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

                // get kota name Npwp
                $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
                $city_NPWP_nama = $city_NPWP->NAMA;
                $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
                $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                $province_NPWP_nama = $province_NPWP->NAMA;
                $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
                $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
                $customer->NAMA_JENIS_USAHA = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
                $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
                $customer->NAMA_SALES = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
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
        $customers = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($customers as $customer) {
                $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
                //get kota name
                $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
                $city_KTP_nama = $city_KTP->NAMA;
                $customer->NAMA_KOTA_KTP = $city_KTP_nama;
                $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                $province_KTP_nama = $province_KTP->NAMA;
                $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
                $country_KTP_kode = $province_KTP->KODE_NEGARA;
                $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

                // get kota name Npwp
                $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
                $city_NPWP_nama = $city_NPWP->NAMA;
                $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
                $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                $province_NPWP_nama = $province_NPWP->NAMA;
                $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
                $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
                $customer->NAMA_JENIS_USAHA = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
                $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
                $customer->NAMA_SALES = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Customer::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customers->values()->toArray(),
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
            4 => ['customerGroup', 'NAMA'],
            12 => ['cityKTP', 'NAMA'],
            13 => ['cityKTP.province', 'NAMA'],
            14 => ['cityKTP.province.country', 'NAMA'],
            23 => ['cityNPWP', 'NAMA'],
            24 => ['cityNPWP.province', 'NAMA'],
            25 => ['cityNPWP.province.country', 'NAMA'],
            43 => ['businessType', 'NAMA'],
            44 => ['salesStaff', 'NAMA'],
            46 => ['arStaff', 'NAMA'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Customer::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = ["TGL_REG"]; // Add your date column names here

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
                foreach ($allData as $customer) {
                    $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
                    //get kota name
                    $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
                    $city_KTP_nama = $city_KTP->NAMA;
                    $customer->NAMA_KOTA_KTP = $city_KTP_nama;
                    $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                    $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                    $province_KTP_nama = $province_KTP->NAMA;
                    $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
                    $country_KTP_kode = $province_KTP->KODE_NEGARA;
                    $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
                    $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

                    // get kota name Npwp
                    $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
                    $city_NPWP_nama = $city_NPWP->NAMA;
                    $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
                    $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                    $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                    $province_NPWP_nama = $province_NPWP->NAMA;
                    $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
                    $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                    $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
                    $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
                    $customer->NAMA_JENIS_USAHA = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
                    $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
                    $customer->NAMA_SALES = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
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

            $customers = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($customers as $customer) {
                    $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
                    //get kota name
                    $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
                    $city_KTP_nama = $city_KTP->NAMA;
                    $customer->NAMA_KOTA_KTP = $city_KTP_nama;
                    $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                    $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                    $province_KTP_nama = $province_KTP->NAMA;
                    $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
                    $country_KTP_kode = $province_KTP->KODE_NEGARA;
                    $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
                    $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

                    // get kota name Npwp
                    $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
                    $city_NPWP_nama = $city_NPWP->NAMA;
                    $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
                    $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                    $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                    $province_NPWP_nama = $province_NPWP->NAMA;
                    $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
                    $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                    $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
                    $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
                    $customer->NAMA_JENIS_USAHA = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
                    $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
                    $customer->NAMA_SALES = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Customer::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $customers->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $User = Customer::where('KODE', $KODE)->first();
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

    public function index2()
    {
        try {
            $customers = Customer::all();
            foreach ($customers as $customer) {
                $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
                //get kota name
                $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
                $city_KTP_nama = $city_KTP->NAMA;
                $customer->NAMA_KOTA_KTP = $city_KTP_nama;
                $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                $province_KTP_nama = $province_KTP->NAMA;
                $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
                $country_KTP_kode = $province_KTP->KODE_NEGARA;
                $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

                // get kota name Npwp
                $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
                $city_NPWP_nama = $city_NPWP->NAMA;
                $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
                $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                $province_NPWP_nama = $province_NPWP->NAMA;
                $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
                $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
                $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
                $customer->NAMA_JENIS_USAHA = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
                $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
                $customer->NAMA_SALES = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully', $customers);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:customers,NAMA',
            'BADAN_HUKUM' => 'required',
            'KODE_GROUP' => 'required',

            'NO_KTP' => 'required',
            'NAMA_KTP' => 'required',
            'ALAMAT_KTP' => 'required',
            'RT_KTP' => 'required',
            'RW_KTP' => 'required',
            'KELURAHAN_KTP' => 'required',
            'KECAMATAN_KTP' => 'required',
            'KODE_KOTA_KTP' => 'required',
            'JENIS' => 'required',
            'NAMA_NPWP' => 'required',
            'NO_NPWP' => 'required',
            'ALAMAT_NPWP' => 'required',
            'RT_NPWP' => 'required',
            'RW_NPWP' => 'required',
            'KELURAHAN_NPWP' => 'required',
            'KECAMATAN_NPWP' => 'required',
            'KODE_KOTA_NPWP' => 'required',
            'CONTACT_PERSON_1' => 'required',
            'JABATAN_1' => 'required',
            'NO_HP_1' => 'required',
            'EMAIL_1' => 'required',
            // 'CONTACT_PERSON_2' => 'required',
            // 'JABATAN_2' => 'required',
            // 'NO_HP_2' => 'required',
            // 'EMAIL_2' => 'required',
            'DIBAYAR' => 'required',
            'LOKASI' => 'required',
            'TOP' => 'required|integer',
            'PAYMENT' => 'required',
            'KETERANGAN_TOP' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'WEBSITE' => 'required',
            'EMAIL' => 'required',
            'KODE_AR' => 'required',
            'KODE_SALES' => 'required',
            'KODE_USAHA' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'FOTO_NPWP' => 'mimes:jpeg,png',
            'FORM_CUSTOMER' => 'mimes:doc,docx,pdf,jpeg,png,jpg',
            'PLAFON' => 'required|numeric',
            'TGL_REG' => 'required|date',
        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = Customer::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($existingData) {
            // $message = "Same data already exists with KODE " . $existingData->KODE . " and NAMA " . $existingData->NAMA . ". Do you still want to continue?";
            // // Data already exists, return a warning message
            // return ApiResponse::json(false, $message, null, 409);
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $foto_ktp = $request->file('FOTO_KTP');
            $foto_npwp = $request->file('FOTO_NPWP');
            $form_customer = $request->file('FORM_CUSTOMER');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_npwp_name = null;
            $form_customer_name = null;
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

            if ($form_customer) {
                $form_customer_name = $form_customer->getClientOriginalName();
                $form_customer_name = 'FORM_CUST' . '_' . uniqid() . '_' . $form_customer_name;
                $form_customer->storeAs('files', $form_customer_name);
            }

            $newCustomer = new Customer();
            $newCustomer->KODE = $this->getLocalNextId();
            $newCustomer->NAMA = $request->get('NAMA');
            $newCustomer->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $newCustomer->KODE_GROUP = $request->get('KODE_GROUP');

            $newCustomer->NO_KTP = $request->get('NO_KTP');
            $newCustomer->NAMA_KTP = $request->get('NAMA_KTP');
            $newCustomer->ALAMAT_KTP = $request->get('ALAMAT_KTP');
            $newCustomer->RT_KTP = $request->get('RT_KTP');
            $newCustomer->RW_KTP = $request->get('RW_KTP');
            $newCustomer->KELURAHAN_KTP = $request->get('KELURAHAN_KTP');
            $newCustomer->KECAMATAN_KTP = $request->get('KECAMATAN_KTP');
            $newCustomer->KODE_KOTA_KTP = $request->get('KODE_KOTA_KTP');
            $newCustomer->JENIS = $request->get('JENIS');
            $newCustomer->NAMA_NPWP = $request->get('NAMA_NPWP');
            $newCustomer->NO_NPWP = $request->get('NO_NPWP');
            $newCustomer->ALAMAT_NPWP = $request->get('ALAMAT_NPWP');
            $newCustomer->RT_NPWP = $request->get('RT_NPWP');
            $newCustomer->RW_NPWP = $request->get('RW_NPWP');
            $newCustomer->KELURAHAN_NPWP = $request->get('KELURAHAN_NPWP');
            $newCustomer->KECAMATAN_NPWP = $request->get('KECAMATAN_NPWP');
            $newCustomer->KODE_KOTA_NPWP = $request->get('KODE_KOTA_NPWP');
            $newCustomer->CONTACT_PERSON_1 = $request->get('CONTACT_PERSON_1');
            $newCustomer->JABATAN_1 = $request->get('JABATAN_1');
            $newCustomer->NO_HP_1 = $request->get('NO_HP_1');
            $newCustomer->EMAIL_1 = $request->get('EMAIL_1');
            $newCustomer->CONTACT_PERSON_2 = $request->get('CONTACT_PERSON_2');
            $newCustomer->JABATAN_2 = $request->get('JABATAN_2');
            $newCustomer->NO_HP_2 = $request->get('NO_HP_2');
            $newCustomer->EMAIL_2 = $request->get('EMAIL_2');
            $newCustomer->DIBAYAR = $request->get('DIBAYAR');
            $newCustomer->LOKASI = $request->get('LOKASI');
            $newCustomer->TOP = $request->get('TOP');
            $newCustomer->PAYMENT = $request->get('PAYMENT');
            $newCustomer->KETERANGAN_TOP = $request->get('KETERANGAN_TOP');
            $newCustomer->TELP = $request->get('TELP');
            $newCustomer->HP = $request->get('HP');
            $newCustomer->WEBSITE = $request->get('WEBSITE');
            $newCustomer->EMAIL = $request->get('EMAIL');
            $newCustomer->KODE_AR = $request->get('KODE_AR');
            $newCustomer->KODE_SALES = $request->get('KODE_SALES');
            $newCustomer->KODE_USAHA = $request->get('KODE_USAHA');
            $newCustomer->PLAFON = $request->get('PLAFON');
            $newCustomer->TGL_REG = $request->get('TGL_REG');
            //request get file
            if ($foto_ktp) {
                $newCustomer->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_npwp) {
                $newCustomer->FOTO_NPWP = $foto_npwp_name;
            }
            if ($form_customer) {
                $newCustomer->FORM_CUSTOMER = $form_customer_name;
            }

            $newCustomer->save();


            if ($newCustomer) {
                //get kota name
                $city_KTP = City::where('KODE', $newCustomer->KODE_KOTA_KTP)->first();
                $city_KTP_nama = $city_KTP->NAMA;
                $province_KTP_kode = $city_KTP->KODE_PROVINSI;
                $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
                $province_KTP_nama = $province_KTP->NAMA;
                $country_KTP_kode = $province_KTP->KODE_NEGARA;
                $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;

                // get kota name Npwp
                $city_NPWP = City::where('KODE', $newCustomer->KODE_KOTA_NPWP)->first();
                $city_NPWP_nama = $city_NPWP->NAMA;
                $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
                $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
                $province_NPWP_nama = $province_NPWP->NAMA;
                $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
                $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;


                //nama group
                $customer_group_nama = CustomerGroup::where('KODE', $newCustomer->KODE_GROUP)->first()->NAMA;
                //nama ar
                $ar_nama = Staff::where('KODE', $newCustomer->KODE_AR)->first()->NAMA;
                //nama sales
                $sales_nama = Staff::where('KODE', $newCustomer->KODE_SALES)->first()->NAMA;
                //nama usaha
                $usaha_nama = BusinessType::where('KODE', $newCustomer->KODE_USAHA)->first()->NAMA;
                // create associative array
                $resp_customer = Customer::where('KODE', $newCustomer->KODE)->first()->toArray();
                $resp_customer['NAMA_KOTA_KTP'] = $city_KTP_nama;
                $resp_customer['NAMA_PROVINSI_KTP'] = $province_KTP_nama;
                $resp_customer['NAMA_NEGARA_KTP'] = $country_KTP_nama;
                $resp_customer['NAMA_KOTA_NPWP'] = $city_NPWP_nama;
                $resp_customer['NAMA_PROVINSI_NPWP'] = $province_NPWP_nama;
                $resp_customer['NAMA_NEGARA_NPWP'] = $country_NPWP_nama;
                $resp_customer['NAMA_GROUP'] = $customer_group_nama;
                $resp_customer['NAMA_AR'] = $ar_nama;
                $resp_customer['NAMA_SALES'] = $sales_nama;
                $resp_customer['NAMA_JENIS_USAHA'] = $usaha_nama;
                return ApiResponse::json(true, "Data inserted successfully with KODE $newCustomer->KODE", $resp_customer);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:customers,NAMA,' . $KODE . ',KODE',
            'BADAN_HUKUM' => 'required',
            'KODE_GROUP' => 'required',

            'NO_KTP' => 'required',
            'NAMA_KTP' => 'required',
            'ALAMAT_KTP' => 'required',
            'RT_KTP' => 'required',
            'RW_KTP' => 'required',
            'KELURAHAN_KTP' => 'required',
            'KECAMATAN_KTP' => 'required',
            'KODE_KOTA_KTP' => 'required',
            'JENIS' => 'required',
            'NAMA_NPWP' => 'required',
            'NO_NPWP' => 'required',
            'ALAMAT_NPWP' => 'required',
            'RT_NPWP' => 'required',
            'RW_NPWP' => 'required',
            'KELURAHAN_NPWP' => 'required',
            'KECAMATAN_NPWP' => 'required',
            'KODE_KOTA_NPWP' => 'required',
            'CONTACT_PERSON_1' => 'required',
            'JABATAN_1' => 'required',
            'NO_HP_1' => 'required',
            'EMAIL_1' => 'required',
            // 'CONTACT_PERSON_2' => 'required',
            // 'JABATAN_2' => 'required',
            // 'NO_HP_2' => 'required',
            // 'EMAIL_2' => 'required',
            'DIBAYAR' => 'required',
            'LOKASI' => 'required',
            'TOP' => 'required|integer',
            'PAYMENT' => 'required',
            'KETERANGAN_TOP' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'WEBSITE' => 'required',
            'EMAIL' => 'required',
            'KODE_AR' => 'required',
            'KODE_SALES' => 'required',
            'KODE_USAHA' => 'required',
            'FOTO_KTP' => 'mimes:jpeg,png',
            'FOTO_NPWP' => 'mimes:jpeg,png',
            'FORM_CUSTOMER' => 'mimes:doc,docx,pdf,jpeg,png,jpg',
            'PLAFON' => 'required|numeric',
            'TGL_REG' => 'required|date',

        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = Customer::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            // $message = "Same data already exists with KODE " . $existingData->KODE . " and NAMA " . $existingData->NAMA . ". Do you still want to continue?";
            // // Data already exists, return a warning message
            // return ApiResponse::json(false, $message, null, 409);
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            //request file if exist
            $foto_ktp = $request->file('FOTO_KTP');
            $foto_npwp = $request->file('FOTO_NPWP');
            $form_customer = $request->file('FORM_CUSTOMER');
            //if file exist then save
            $foto_ktp_name = null;
            $foto_npwp_name = null;
            $form_customer_name = null;
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

            if ($form_customer) {
                $form_customer_name = $form_customer->getClientOriginalName();
                $form_customer_name = 'FORM_CUST' . '_' . uniqid() . '_' . $form_customer_name;
                $form_customer->storeAs('files', $form_customer_name);
            }






            $customer = Customer::findOrFail($KODE);
            if ($customer->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update customer because it has related records', null, 400);
            }


            // $customer->KODE = Customer::withTrashed()->max('KODE') + 1;
            $customer->NAMA = $request->get('NAMA');
            $customer->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $customer->KODE_GROUP = $request->get('KODE_GROUP');

            $customer->NO_KTP = $request->get('NO_KTP');
            $customer->NAMA_KTP = $request->get('NAMA_KTP');
            $customer->ALAMAT_KTP = $request->get('ALAMAT_KTP');
            $customer->RT_KTP = $request->get('RT_KTP');
            $customer->RW_KTP = $request->get('RW_KTP');
            $customer->KELURAHAN_KTP = $request->get('KELURAHAN_KTP');
            $customer->KECAMATAN_KTP = $request->get('KECAMATAN_KTP');
            $customer->KODE_KOTA_KTP = $request->get('KODE_KOTA_KTP');
            $customer->JENIS = $request->get('JENIS');
            $customer->NAMA_NPWP = $request->get('NAMA_NPWP');
            $customer->NO_NPWP = $request->get('NO_NPWP');
            $customer->ALAMAT_NPWP = $request->get('ALAMAT_NPWP');
            $customer->RT_NPWP = $request->get('RT_NPWP');
            $customer->RW_NPWP = $request->get('RW_NPWP');
            $customer->KELURAHAN_NPWP = $request->get('KELURAHAN_NPWP');
            $customer->KECAMATAN_NPWP = $request->get('KECAMATAN_NPWP');
            $customer->KODE_KOTA_NPWP = $request->get('KODE_KOTA_NPWP');
            $customer->CONTACT_PERSON_1 = $request->get('CONTACT_PERSON_1');
            $customer->JABATAN_1 = $request->get('JABATAN_1');
            $customer->NO_HP_1 = $request->get('NO_HP_1');
            $customer->EMAIL_1 = $request->get('EMAIL_1');
            $customer->CONTACT_PERSON_2 = $request->get('CONTACT_PERSON_2');
            $customer->JABATAN_2 = $request->get('JABATAN_2');
            $customer->NO_HP_2 = $request->get('NO_HP_2');
            $customer->EMAIL_2 = $request->get('EMAIL_2');
            $customer->DIBAYAR = $request->get('DIBAYAR');
            $customer->LOKASI = $request->get('LOKASI');
            $customer->TOP = $request->get('TOP');
            $customer->PAYMENT = $request->get('PAYMENT');
            $customer->KETERANGAN_TOP = $request->get('KETERANGAN_TOP');
            $customer->TELP = $request->get('TELP');
            $customer->HP = $request->get('HP');
            $customer->WEBSITE = $request->get('WEBSITE');
            $customer->EMAIL = $request->get('EMAIL');
            $customer->KODE_AR = $request->get('KODE_AR');
            $customer->KODE_SALES = $request->get('KODE_SALES');
            $customer->KODE_USAHA = $request->get('KODE_USAHA');
            $customer->PLAFON = $request->get('PLAFON');
            //get date now for timestamp
            $customer->TGL_REG = $request->get('TGL_REG');
            //request get file
            if ($foto_ktp) {
                $customer->FOTO_KTP = $foto_ktp_name;
            }
            if ($foto_npwp) {
                $customer->FOTO_NPWP = $foto_npwp_name;
            }
            if ($form_customer) {
                $customer->FORM_CUSTOMER = $form_customer_name;
            }
            // $customer->FOTO_KTP = $foto_ktp_name;
            // $customer->FOTO_NPWP = $foto_npwp_name;
            // $customer->FORM_CUSTOMER = $form_customer_name;

            $customer->save();

            if (!$customer) {
                return ApiResponse::json(false, 'Failed to update customer', null, 500);
            }

            $customer = Customer::where('KODE', $KODE)->first();


            //get kota name
            $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
            $city_KTP_nama = $city_KTP->NAMA;
            $customer->NAMA_KOTA_KTP = $city_KTP_nama;
            $province_KTP_kode = $city_KTP->KODE_PROVINSI;
            $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
            $province_KTP_nama = $province_KTP->NAMA;
            $customer->NAMA_PROVINSI_KTP = $province_KTP_nama;
            $country_KTP_kode = $province_KTP->KODE_NEGARA;
            $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
            $customer->NAMA_NEGARA_KTP = $country_KTP_nama;

            // get kota name Npwp
            $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
            $city_NPWP_nama = $city_NPWP->NAMA;
            $customer->NAMA_KOTA_NPWP = $city_NPWP_nama;
            $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
            $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
            $province_NPWP_nama = $province_NPWP->NAMA;
            $customer->NAMA_PROVINSI_NPWP = $province_NPWP_nama;
            $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
            $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
            $customer->NAMA_NEGARA_NPWP = $country_NPWP_nama;
            //nama group
            $customer_group_nama = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
            $customer->NAMA_GROUP = $customer_group_nama;
            //nama ar
            $ar_nama = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
            $customer->NAMA_AR = $ar_nama;
            //nama sales
            $sales_nama = Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA;
            $customer->NAMA_SALES = $sales_nama;
            //nama usaha
            $usaha_nama = BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA;
            $customer->NAMA_JENIS_USAHA = $usaha_nama;

            //convert to array
            $customer = $customer->toArray();

            return ApiResponse::json(true, 'Customer successfully updated', $customer);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $customer = Customer::findOrFail($KODE);
            $customer->delete();
            return ApiResponse::json(true, 'Customer successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    // public function trash()
    // {
    //     $customers = Customer::all(); // Get all customers

    //     foreach ($customers as $customer) {
    //         $city = City::where('KODE', $customer->KODE_KOTA)->first();
    //         // get the province based on the city
    //         $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
    //         // get the country based on the province
    //         $country = Country::where('KODE', $province->KODE_NEGARA)->first();

    //         $customer->NAMA_GROUP = CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA;
    //         $customer->NAMA_AR = Staff::where('KODE', $customer->KODE_AR)->first()->NAMA;
    //     }

    //     $deletedCustomers = $customers->filter(function ($customer) {
    //         return $customer->deleted_at !== null; // Filter customers with non-null deleted_at field
    //     });

    //     return ApiResponse::json(true, 'Trash bin fetched', $deletedCustomers);
    // }

    public function trash()
    {
        $customers = Customer::onlyTrashed()->get();

        foreach ($customers as $customer) {
            //get kota name
            $city_KTP = City::where('KODE', $customer->KODE_KOTA_KTP)->first();
            $city_KTP_nama = $city_KTP->NAMA;
            $customer->setAttribute('NAMA_KOTA_KTP', $city_KTP_nama);
            $province_KTP_kode = $city_KTP->KODE_PROVINSI;
            $province_KTP = Province::where('KODE', $province_KTP_kode)->first();
            $province_KTP_nama = $province_KTP->NAMA;
            $customer->setAttribute('NAMA_PROVINSI_KTP', $province_KTP_nama);
            $country_KTP_kode = $province_KTP->KODE_NEGARA;
            $country_KTP_nama = Country::where('KODE', $country_KTP_kode)->first()->NAMA;
            $customer->setAttribute('NAMA_NEGARA_KTP', $country_KTP_nama);

            // get kota name Npwp
            $city_NPWP = City::where('KODE', $customer->KODE_KOTA_NPWP)->first();
            $city_NPWP_nama = $city_NPWP->NAMA;
            $customer->setAttribute('NAMA_KOTA_NPWP', $city_NPWP_nama);
            $province_NPWP_kode = $city_NPWP->KODE_PROVINSI;
            $province_NPWP = Province::where('KODE', $province_NPWP_kode)->first();
            $province_NPWP_nama = $province_NPWP->NAMA;
            $customer->setAttribute('NAMA_PROVINSI_NPWP', $province_NPWP_nama);
            $country_NPWP_kode = $province_NPWP->KODE_NEGARA;
            $country_NPWP_nama = Country::where('KODE', $country_NPWP_kode)->first()->NAMA;
            $customer->setAttribute('NAMA_NEGARA_NPWP', $country_NPWP_nama);

            $customer->setAttribute('NAMA_GROUP', CustomerGroup::where('KODE', $customer->KODE_GROUP)->first()->NAMA);
            $customer->setAttribute('NAMA_AR', Staff::where('KODE', $customer->KODE_AR)->first()->NAMA);
            // set attribute nama_sales dan nama_jenis_usaha
            $customer->setAttribute('NAMA_SALES', Staff::where('KODE', $customer->KODE_SALES)->first()->NAMA);
            $customer->setAttribute('NAMA_JENIS_USAHA', BusinessType::where('KODE', $customer->KODE_USAHA)->first()->NAMA);
        }

        return ApiResponse::json(true, 'Trash bin fetched', $customers);
    }



    public function restore($id)
    {
        $restored = Customer::onlyTrashed()->findOrFail($id);
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
        // Get the maximum JBT.X ID from the database
        $maxId = Customer::where('KODE', 'LIKE', 'MIT.%')
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

        // Pad the number portion of the ID with leading zeros
        $paddedNumber = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        // Create the next ID by concatenating 'JBT.' with the padded number
        $nextId = 'MIT.' . $paddedNumber;

        return $nextId;
    }
}
