<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Country;
use App\Models\CustomerGroup;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerGroupController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = CustomerGroup::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = CustomerGroup::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [

                'city' => [
                    'column' => 'NAMA',
                ],
                'city.province' => [
                    'column' => 'NAMA',
                ],
                'city.province.country' => [
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
            foreach ($allData as $customer_group) {
                $city = City::where('KODE', $customer_group->KODE_KOTA)->first();
                $customer_group->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $customer_group->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $customer_group->NAMA_NEGARA = $country->NAMA;
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
        $customer_groups = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($customer_groups as $customer_group) {
                $city = City::where('KODE', $customer_group->KODE_KOTA)->first();
                $customer_group->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $customer_group->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $customer_group->NAMA_NEGARA = $country->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => CustomerGroup::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customer_groups->values()->toArray(),
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
            5 => ['city', 'NAMA'],
            6 => ['city.province', 'NAMA'],
            7 => ['city.province.country', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = CustomerGroup::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


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
                                $query->where($columnName, 'LIKE', "%$searchValue%");
                            }
                        }
                    }
                }
            }

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $customer_group) {
                    $city = City::where('KODE', $customer_group->KODE_KOTA)->first();
                    $customer_group->NAMA_KOTA = $city->NAMA;
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $customer_group->NAMA_PROVINSI = $province->NAMA;
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $customer_group->NAMA_NEGARA = $country->NAMA;
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

            $customer_groups = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($customer_groups as $customer_group) {
                    $city = City::where('KODE', $customer_group->KODE_KOTA)->first();
                    $customer_group->NAMA_KOTA = $city->NAMA;
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $customer_group->NAMA_PROVINSI = $province->NAMA;
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $customer_group->NAMA_NEGARA = $country->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => CustomerGroup::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $customer_groups->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $User = CustomerGroup::where('KODE', $KODE)->first();
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

    public function getKota()
    {
        $kota = City::all();
        return response()->json([
            'kota' => $kota
        ]);
    }



    // public function index()
    // {
    //     try {
    //         $customer_group = CustomerGroup::orderBy('KODE', 'asc')->get();


    //         return ApiResponse::json(true, 'Data retrieved successfully',  $customer_group);
    //     } catch (\Exception $e) {
    //         return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
    //     }
    // }

    public function indexWeb()
    {
        try {
            $customer_groups = CustomerGroup::orderBy('KODE', 'asc')->get();

            foreach ($customer_groups as $customer_group) {
                $city = City::where('KODE', $customer_group->KODE_KOTA)->first();
                $customer_group->NAMA_KOTA = $city->NAMA;
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $customer_group->NAMA_PROVINSI = $province->NAMA;
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $customer_group->NAMA_NEGARA = $country->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $customer_groups);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }



    public function store(Request $request)
    {
        // Use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:customer_groups,NAMA',
            'BADAN_HUKUM' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'EMAIL' => 'required',
            'FAX' => 'required',
            'CONTACT_PERSON' => 'required',
            'NO_HP_CP' => 'required',
            'NO_SMS_CP' => 'required',
            'KETERANGAN' => 'required',
            'WEBSITE' => 'required',
            'EMAIL1' => 'required',
            'AKTIF' => 'required|boolean',
        ], [

            'NAMA.required' => 'The NAMA field is required.',
            'BADAN_HUKUM.required' => 'The BADAN_HUKUM field is required.',
            'ALAMAT.required' => 'The ALAMAT field is required.',
            'KODE_KOTA.required' => 'The KODE_KOTA field is required.',

            'TELP.required' => 'The TELP field is required.',
            'HP.required' => 'The HP field is required.',
            'EMAIL.required' => 'The EMAIL field is required.',
            'FAX.required' => 'The FAX field is required.',
            'CONTACT_PERSON.required' => 'The CONTACT_PERSON field is required.',
            'NO_HP_CP.required' => 'The NO_HP_CP field is required.',
            'NO_SMS_CP.required' => 'The NO_SMS_CP field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
            'WEBSITE.required' => 'The WEBSITE field is required.',
            'AKTIF.required' => 'The AKTIF field is required.',
        ]);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        // Check if the client has input the same data
        $existingData = CustomerGroup::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();

        if ($existingData) {
            // $message = "Same data already exists with KODE " . $existingData->KODE . " and NAMA " . $existingData->NAMA . ". Do you still want to continue?";
            // // Data already exists, return a warning message
            // return ApiResponse::json(false, $message, null, 409);
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $city = City::where('KODE', $request->get('KODE_KOTA'))->first();
            if (!$city) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', null, 400);
            }

            $province_kode = $city->KODE_PROVINSI;

            $province = Province::where('KODE', $province_kode)->first();
            $province_nama = $province->NAMA;
            $country_kode = $province->KODE_NEGARA;
            $country_nama = Country::where('KODE', $country_kode)->first()->NAMA;

            $new_customer_group = new CustomerGroup();
            $new_customer_group->KODE = $this->getLocalNextId();

            $new_customer_group->NAMA = $request->get('NAMA');
            $new_customer_group->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $new_customer_group->ALAMAT = $request->get('ALAMAT');
            $new_customer_group->KODE_KOTA = $request->get('KODE_KOTA');
            // $new_customer_group->KODE_PROVINSI = $province_kode;
            // $new_customer_group->KODE_NEGARA = $country_kode;
            $new_customer_group->TELP = $request->get('TELP');
            $new_customer_group->HP = $request->get('HP');
            $new_customer_group->EMAIL = $request->get('EMAIL');
            $new_customer_group->FAX = $request->get('FAX');
            $new_customer_group->CONTACT_PERSON = $request->get('CONTACT_PERSON');
            $new_customer_group->NO_HP_CP = $request->get('NO_HP_CP');
            $new_customer_group->NO_SMS_CP = $request->get('NO_SMS_CP');
            $new_customer_group->KETERANGAN = $request->get('KETERANGAN');
            $new_customer_group->WEBSITE = $request->get('WEBSITE');
            $new_customer_group->EMAIL1 = $request->get('EMAIL1');
            $new_customer_group->AKTIF = $request->get('AKTIF');
            $new_customer_group->save();




            // check if save success, then return call the function to get customer group by kode
            if ($new_customer_group) {
                // create associative array
                $resp_customer_group = array(
                    'KODE' => $new_customer_group->KODE,
                    'NAMA' => $new_customer_group->NAMA,
                    'BADAN_HUKUM' => $new_customer_group->BADAN_HUKUM,
                    'ALAMAT' => $new_customer_group->ALAMAT,
                    'NAMA_KOTA' => $city->NAMA,
                    'NAMA_PROVINSI' => $province_nama,
                    'NAMA_NEGARA' => $country_nama,
                    'TELP' => $new_customer_group->TELP,
                    'HP' => $new_customer_group->HP,
                    'EMAIL' => $new_customer_group->EMAIL,
                    'FAX' => $new_customer_group->FAX,
                    'CONTACT_PERSON' => $new_customer_group->CONTACT_PERSON,
                    'NO_HP_CP' => $new_customer_group->NO_HP_CP,
                    'NO_SMS_CP' => $new_customer_group->NO_SMS_CP,
                    // if aktif is 1 then return Y, else return N
                    'AKTIF' => $new_customer_group->AKTIF == 1 ? 'Y' : 'N',
                    'KETERANGAN' => $new_customer_group->KETERANGAN,
                    'WEBSITE' => $new_customer_group->WEBSITE,
                    'EMAIL1' => $new_customer_group->EMAIL1,
                );
                return ApiResponse::json(true, "Data inserted successfully with KODE $new_customer_group->KODE", $resp_customer_group, 201);
            } else {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function storeContinue(Request $request)
    {
        // Use validator here

        $validator = Validator::make($request->all(), [
            'NAMA' => 'required',
            'BADAN_HUKUM' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'EMAIL' => 'required',
            'FAX' => 'required',
            'CONTACT_PERSON' => 'required',
            'NO_HP_CP' => 'required',
            'NO_SMS_CP' => 'required',
            'KETERANGAN' => 'required',
            'WEBSITE' => 'required',
            'EMAIL1' => 'required',
            'AKTIF' => 'required|boolean',
        ], [

            'NAMA.required' => 'The NAMA field is required.',
            'BADAN_HUKUM.required' => 'The BADAN_HUKUM field is required.',
            'ALAMAT.required' => 'The ALAMAT field is required.',
            'KODE_KOTA.required' => 'The KODE_KOTA field is required.',

            'TELP.required' => 'The TELP field is required.',
            'HP.required' => 'The HP field is required.',
            'EMAIL.required' => 'The EMAIL field is required.',
            'FAX.required' => 'The FAX field is required.',
            'CONTACT_PERSON.required' => 'The CONTACT_PERSON field is required.',
            'NO_HP_CP.required' => 'The NO_HP_CP field is required.',
            'NO_SMS_CP.required' => 'The NO_SMS_CP field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
            'WEBSITE.required' => 'The WEBSITE field is required.',
            'EMAIL1.required' => 'The EMAIL1 field is required.',
            'AKTIF.required' => 'The AKTIF field is required.',
        ]);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        try {
            $city = City::where('KODE', $request->get('KODE_KOTA'))->first();
            if (!$city) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', null, 400);
            }

            $province_kode = $city->KODE_PROVINSI;
            $province = Province::where('KODE', $province_kode)->first();
            $province_nama = $province->NAMA;
            $country_kode = $province->KODE_NEGARA;
            $country_nama = Country::where('KODE', $country_kode)->first()->NAMA;
            $new_customer_group = new CustomerGroup();
            $new_customer_group->KODE = CustomerGroup::max('KODE') + 1;
            $new_customer_group->NAMA = $request->get('NAMA');
            $new_customer_group->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $new_customer_group->ALAMAT = $request->get('ALAMAT');
            $new_customer_group->KODE_KOTA = $request->get('KODE_KOTA');
            // $new_customer_group->KODE_PROVINSI = $province_kode;
            // $new_customer_group->KODE_NEGARA = $country_kode;
            $new_customer_group->TELP = $request->get('TELP');
            $new_customer_group->HP = $request->get('HP');
            $new_customer_group->EMAIL = $request->get('EMAIL');
            $new_customer_group->FAX = $request->get('FAX');
            $new_customer_group->CONTACT_PERSON = $request->get('CONTACT_PERSON');
            $new_customer_group->NO_HP_CP = $request->get('NO_HP_CP');
            $new_customer_group->NO_SMS_CP = $request->get('NO_SMS_CP');
            $new_customer_group->KETERANGAN = $request->get('KETERANGAN');
            $new_customer_group->WEBSITE = $request->get('WEBSITE');
            $new_customer_group->EMAIL1 = $request->get('EMAIL1');
            $new_customer_group->AKTIF = $request->get('AKTIF');
            $new_customer_group->save();



            // check if save success, then return call the function to get customer group by kode
            if ($new_customer_group) {
                // create associative array
                $resp_customer_group = array(
                    'KODE' => $new_customer_group->KODE,
                    'NAMA' => $new_customer_group->NAMA,
                    'BADAN_HUKUM' => $new_customer_group->BADAN_HUKUM,
                    'ALAMAT' => $new_customer_group->ALAMAT,
                    'NAMA_KOTA' => $city->NAMA,
                    'NAMA_PROVINSI' => $province_nama,
                    'NAMA_NEGARA' => $country_nama,
                    'TELP' => $new_customer_group->TELP,
                    'HP' => $new_customer_group->HP,
                    'EMAIL' => $new_customer_group->EMAIL,
                    'FAX' => $new_customer_group->FAX,
                    'CONTACT_PERSON' => $new_customer_group->CONTACT_PERSON,
                    'NO_HP_CP' => $new_customer_group->NO_HP_CP,
                    'NO_SMS_CP' => $new_customer_group->NO_SMS_CP,
                    // if aktif is 1 then return Y, else return N
                    'AKTIF' => $new_customer_group->AKTIF == 1 ? 'Y' : 'N',
                    'KETERANGAN' => $new_customer_group->KETERANGAN,
                    'WEBSITE' => $new_customer_group->WEBSITE,
                    'EMAIL1' => $new_customer_group->EMAIL1,
                );
                return ApiResponse::json(true, "Data inserted successfully with KODE $new_customer_group->KODE", $resp_customer_group, 201);
            } else {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }


    public function update(Request $request, $KODE)
    {
        // Use validator here
        $validator = Validator::make($request->all(), [
            // 'KODE_NEGARA' => 'required|numeric',
            'NAMA' => 'required|unique:customer_groups,NAMA,' . $KODE . ',KODE',
            'BADAN_HUKUM' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            // 'KODE_PROVINSI' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'EMAIL' => 'required',
            'FAX' => 'required',
            'CONTACT_PERSON' => 'required',
            'NO_HP_CP' => 'required',
            'NO_SMS_CP' => 'required',
            'KETERANGAN' => 'required',
            'WEBSITE' => 'required',
            'EMAIL1' => 'required',
            'AKTIF' => 'required|boolean',
        ], [
            // 'KODE_NEGARA.required' => 'The KODE_NEGARA field is required.',
            'NAMA.required' => 'The NAMA field is required.',
            'BADAN_HUKUM.required' => 'The BADAN_HUKUM field is required.',
            'ALAMAT.required' => 'The ALAMAT field is required.',
            'KODE_KOTA.required' => 'The KODE_KOTA field is required.',
            // 'KODE_PROVINSI.required' => 'The KODE_PROVINSI field is required.',
            'TELP.required' => 'The TELP field is required.',
            'HP.required' => 'The HP field is required.',
            'EMAIL.required' => 'The EMAIL field is required.',
            'FAX.required' => 'The FAX field is required.',
            'CONTACT_PERSON.required' => 'The CONTACT_PERSON field is required.',
            'NO_HP_CP.required' => 'The NO_HP_CP field is required.',
            'NO_SMS_CP.required' => 'The NO_SMS_CP field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
            'WEBSITE.required' => 'The WEBSITE field is required.',
            'EMAIL1.required' => 'The EMAIL1 field is required.',
            'AKTIF.required' => 'The AKTIF field is required.',
        ]);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }
        // Check uniqueness for NAMA case insensitive except for current KODE
        $existingData = CustomerGroup::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $customer_group = CustomerGroup::findOrFail($KODE);
            if ($customer_group->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update truck route because it has related records', null, 400);
            }
            $city = City::where('KODE', $request->get('KODE_KOTA'))->first();
            if (!$city) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', null, 400);
            }
            $province_kode = $city->KODE_PROVINSI;
            $province_nama = Province::where('KODE', $province_kode)->first()->NAMA;
            $country_nama = Country::where('KODE', $province_kode)->first()->NAMA;
            $customer_group->NAMA = $request->get('NAMA');
            $customer_group->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $customer_group->ALAMAT = $request->get('ALAMAT');
            $customer_group->KODE_KOTA = $request->get('KODE_KOTA');
            // $customer_group->KODE_PROVINSI = $province_kode;
            // $customer_group->KODE_NEGARA = $country_kode;
            $customer_group->TELP = $request->get('TELP');
            $customer_group->HP = $request->get('HP');
            $customer_group->EMAIL = $request->get('EMAIL');
            $customer_group->FAX = $request->get('FAX');
            $customer_group->CONTACT_PERSON = $request->get('CONTACT_PERSON');
            $customer_group->NO_HP_CP = $request->get('NO_HP_CP');
            $customer_group->NO_SMS_CP = $request->get('NO_SMS_CP');
            $customer_group->KETERANGAN = $request->get('KETERANGAN');
            $customer_group->WEBSITE = $request->get('WEBSITE');
            $customer_group->EMAIL1 = $request->get('EMAIL1');
            $customer_group->AKTIF = $request->get('AKTIF');
            $customer_group->save();
            if (!$customer_group) {
                return ApiResponse::json(false, 'Failed to update customer group', null, 500);
            }
            $resp_customer_group = array(
                'KODE' => $customer_group->KODE,
                'NAMA' => $customer_group->NAMA,
                'BADAN_HUKUM' => $customer_group->BADAN_HUKUM,
                'ALAMAT' => $customer_group->ALAMAT,
                'NAMA_KOTA' => $city->NAMA,
                'NAMA_PROVINSI' => $province_nama,
                'NAMA_NEGARA' => $country_nama,
                'TELP' => $customer_group->TELP,
                'HP' => $customer_group->HP,
                'EMAIL' => $customer_group->EMAIL,
                'FAX' => $customer_group->FAX,
                'CONTACT_PERSON' => $customer_group->CONTACT_PERSON,
                'NO_HP_CP' => $customer_group->NO_HP_CP,
                'NO_SMS_CP' => $customer_group->NO_SMS_CP,
                // if aktif is 1 then return Y, else return N
                'AKTIF' => $customer_group->AKTIF == 1 ? 'Y' : 'N',
                'KETERANGAN' => $customer_group->KETERANGAN,
                'WEBSITE' => $customer_group->WEBSITE,
                'EMAIL1' => $customer_group->EMAIL1,
            );
            return ApiResponse::json(true, 'Customer group successfully updated', $resp_customer_group);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function updateContinue(Request $request, $KODE)
    {
        // Use validator here
        $validator = Validator::make($request->all(), [
            // 'KODE_NEGARA' => 'required|numeric',
            'NAMA' => 'required',
            'BADAN_HUKUM' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            // 'KODE_PROVINSI' => 'required',
            'TELP' => 'required',
            'HP' => 'required',
            'EMAIL' => 'required',
            'FAX' => 'required',
            'CONTACT_PERSON' => 'required',
            'NO_HP_CP' => 'required',
            'NO_SMS_CP' => 'required',
            'KETERANGAN' => 'required',
            'WEBSITE' => 'required',
            'EMAIL1' => 'required',
            'AKTIF' => 'required|boolean',
        ], [
            // 'KODE_NEGARA.required' => 'The KODE_NEGARA field is required.',
            'NAMA.required' => 'The NAMA field is required.',
            'BADAN_HUKUM.required' => 'The BADAN_HUKUM field is required.',
            'ALAMAT.required' => 'The ALAMAT field is required.',
            'KODE_KOTA.required' => 'The KODE_KOTA field is required.',
            // 'KODE_PROVINSI.required' => 'The KODE_PROVINSI field is required.',
            'TELP.required' => 'The TELP field is required.',
            'HP.required' => 'The HP field is required.',
            'EMAIL.required' => 'The EMAIL field is required.',
            'FAX.required' => 'The FAX field is required.',
            'CONTACT_PERSON.required' => 'The CONTACT_PERSON field is required.',
            'NO_HP_CP.required' => 'The NO_HP_CP field is required.',
            'NO_SMS_CP.required' => 'The NO_SMS_CP field is required.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
            'WEBSITE.required' => 'The WEBSITE field is required.',
            'EMAIL1.required' => 'The EMAIL1 field is required.',
            'AKTIF.required' => 'The AKTIF field is required.',
        ]);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }
        try {
            $customer_group = CustomerGroup::findOrFail($KODE);
            $city = City::where('KODE', $request->get('KODE_KOTA'))->first();
            if (!$city) {
                return ApiResponse::json(false, 'Invalid KODE_KOTA', null, 400);
            }
            $province_kode = $city->KODE_PROVINSI;
            $province_nama = Province::where('KODE', $province_kode)->first()->NAMA;
            $country_nama = Country::where('KODE', $province_kode)->first()->NAMA;
            $customer_group->NAMA = $request->get('NAMA');
            $customer_group->BADAN_HUKUM = $request->get('BADAN_HUKUM');
            $customer_group->ALAMAT = $request->get('ALAMAT');
            $customer_group->KODE_KOTA = $request->get('KODE_KOTA');
            // $customer_group->KODE_PROVINSI = $province_kode;
            // $customer_group->KODE_NEGARA = $country_kode;
            $customer_group->TELP = $request->get('TELP');
            $customer_group->HP = $request->get('HP');
            $customer_group->EMAIL = $request->get('EMAIL');
            $customer_group->FAX = $request->get('FAX');
            $customer_group->CONTACT_PERSON = $request->get('CONTACT_PERSON');
            $customer_group->NO_HP_CP = $request->get('NO_HP_CP');
            $customer_group->NO_SMS_CP = $request->get('NO_SMS_CP');
            $customer_group->KETERANGAN = $request->get('KETERANGAN');
            $customer_group->WEBSITE = $request->get('WEBSITE');
            $customer_group->EMAIL1 = $request->get('EMAIL1');
            $customer_group->AKTIF = $request->get('AKTIF');
            $customer_group->save();
            if (!$customer_group) {
                return ApiResponse::json(false, 'Failed to update customer group', null, 500);
            }
            $resp_customer_group = array(
                'KODE' => $customer_group->KODE,
                'NAMA' => $customer_group->NAMA,
                'BADAN_HUKUM' => $customer_group->BADAN_HUKUM,
                'ALAMAT' => $customer_group->ALAMAT,
                'NAMA_KOTA' => $city->NAMA,
                'NAMA_PROVINSI' => $province_nama,
                'NAMA_NEGARA' => $country_nama,
                'TELP' => $customer_group->TELP,
                'HP' => $customer_group->HP,
                'EMAIL' => $customer_group->EMAIL,
                'FAX' => $customer_group->FAX,
                'CONTACT_PERSON' => $customer_group->CONTACT_PERSON,
                'NO_HP_CP' => $customer_group->NO_HP_CP,
                'NO_SMS_CP' => $customer_group->NO_SMS_CP,
                // if aktif is 1 then return Y, else return N
                'AKTIF' => $customer_group->AKTIF == 1 ? 'Y' : 'N',
                'KETERANGAN' => $customer_group->KETERANGAN,
                'WEBSITE' => $customer_group->WEBSITE,
                'EMAIL1' => $customer_group->EMAIL1,
            );
            return ApiResponse::json(true, 'Customer group successfully updated', $resp_customer_group);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $customer_group = CustomerGroup::findOrFail($KODE);
            $customer_group->delete();
            return ApiResponse::json(true, 'Customer group successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $customerGroups = CustomerGroup::onlyTrashed()->get();

        $respCustomerGroups = [];

        foreach ($customerGroups as $customerGroup) {
            $city = City::where('KODE', $customerGroup->KODE_KOTA)->first();
            // get the province based on the city
            $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
            // get the country based on the province
            $country = Country::where('KODE', $province->KODE_NEGARA)->first();

            $respCustomerGroup = [
                'KODE' => $customerGroup->KODE,
                'NAMA' => $customerGroup->NAMA,
                'BADAN_HUKUM' => $customerGroup->BADAN_HUKUM,
                'ALAMAT' => $customerGroup->ALAMAT,
                'NAMA_KOTA' => $city ? $city->NAMA : null,
                'NAMA_PROVINSI' => $province ? $province->NAMA : null,
                'NAMA_NEGARA' => $country ? $country->NAMA : null,
                'TELP' => $customerGroup->TELP,
                'HP' => $customerGroup->HP,
                'EMAIL' => $customerGroup->EMAIL,
                'FAX' => $customerGroup->FAX,
                'CONTACT_PERSON' => $customerGroup->CONTACT_PERSON,
                'NO_HP_CP' => $customerGroup->NO_HP_CP,
                'NO_SMS_CP' => $customerGroup->NO_SMS_CP,
                'AKTIF' => $customerGroup->AKTIF == 1 ? 'Y' : 'N',
                'KETERANGAN' => $customerGroup->KETERANGAN,
                'WEBSITE' => $customerGroup->WEBSITE,
                'EMAIL1' => $customerGroup->EMAIL1,
            ];

            $respCustomerGroups[] = $respCustomerGroup;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $respCustomerGroups);
    }



    public function restore($id)
    {
        $restored = CustomerGroup::onlyTrashed()->findOrFail($id);
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
        $maxId = CustomerGroup::where('KODE', 'LIKE', 'GM.%')
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
        $paddedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Create the next ID by concatenating 'JBT.' with the padded number
        $nextId = 'GM.' . $paddedNumber;

        return $nextId;
    }
}
