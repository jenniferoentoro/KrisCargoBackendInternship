<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Account;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Warehouse::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Warehouse::first()->getAttributes(); // Get all attribute names
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
                'city' => [
                    'table' => 'cities',
                    'column' => 'NAMA',
                ],
                'city.province' => [
                    'table' => 'provinces',
                    'column' => 'NAMA',
                ],
                'city.province.country' => [
                    'table' => 'countries',
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
            foreach ($allData as $warehouse) {
                // $warehouse->NAMA_VENDOR = Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA;
                // $user_pic = Staff::where('KODE', $warehouse->KODE_PIC)->first();
                // $warehouse->NAMA_PIC = $user_pic->NAMA;
                // $warehouse->NAMA_ACCOUNT = Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA_ACCOUNT;


                // $warehouse->NO_HP = $user_pic->NO_HP;
                // $warehouse->EMAIL = $user_pic->EMAIL;

                $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
                $nama_kota = $kota->NAMA;
                $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
                $nama_provinsi = $provinsi->NAMA;
                $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
                $nama_negara = $negara->NAMA;

                $warehouse->NAMA_KOTA = $nama_kota;
                $warehouse->NAMA_PROVINSI = $nama_provinsi;
                $warehouse->NAMA_NEGARA = $nama_negara;
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
        $warehouses = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($warehouses as $warehouse) {
                // $warehouse->NAMA_VENDOR = Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA;
                // $user_pic = Staff::where('KODE', $warehouse->KODE_PIC)->first();
                // $warehouse->NAMA_PIC = $user_pic->NAMA;
                // $warehouse->NAMA_ACCOUNT = Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA_ACCOUNT;


                // $warehouse->NO_HP = $user_pic->NO_HP;
                // $warehouse->EMAIL = $user_pic->EMAIL;

                $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
                $nama_kota = $kota->NAMA;
                $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
                $nama_provinsi = $provinsi->NAMA;
                $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
                $nama_negara = $negara->NAMA;

                $warehouse->NAMA_KOTA = $nama_kota;
                $warehouse->NAMA_PROVINSI = $nama_provinsi;
                $warehouse->NAMA_NEGARA = $nama_negara;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Warehouse::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $warehouses->values()->toArray(),
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

            $query = Warehouse::query();

            //check for each column hasSearch columns[x][search][value]
            $columns = $request->input('columns');
            $hasSearch = false;
            foreach ($columns as $index => $column) {
                if (isset($column['search']['value']) && !empty($column['search']['value'])) {
                    $hasSearch = true;
                    break;
                }
            }


            $dateColumns = []; // Add your date column names here

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
                foreach ($allData as $warehouse) {
                    // $warehouse->NAMA_VENDOR = Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA;
                    // $user_pic = Staff::where('KODE', $warehouse->KODE_PIC)->first();
                    // $warehouse->NAMA_PIC = $user_pic->NAMA;
                    // $warehouse->NAMA_ACCOUNT = Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA_ACCOUNT;


                    // $warehouse->NO_HP = $user_pic->NO_HP;
                    // $warehouse->EMAIL = $user_pic->EMAIL;

                    $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
                    $nama_kota = $kota->NAMA;
                    $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
                    $nama_provinsi = $provinsi->NAMA;
                    $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
                    $nama_negara = $negara->NAMA;

                    $warehouse->NAMA_KOTA = $nama_kota;
                    $warehouse->NAMA_PROVINSI = $nama_provinsi;
                    $warehouse->NAMA_NEGARA = $nama_negara;
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

            $warehouses = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($warehouses as $warehouse) {
                    // $warehouse->NAMA_VENDOR = Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA;
                    // $user_pic = Staff::where('KODE', $warehouse->KODE_PIC)->first();
                    // $warehouse->NAMA_PIC = $user_pic->NAMA;
                    // $warehouse->NAMA_ACCOUNT = Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA_ACCOUNT;


                    // $warehouse->NO_HP = $user_pic->NO_HP;
                    // $warehouse->EMAIL = $user_pic->EMAIL;

                    $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
                    $nama_kota = $kota->NAMA;
                    $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
                    $nama_provinsi = $provinsi->NAMA;
                    $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
                    $nama_negara = $negara->NAMA;

                    $warehouse->NAMA_KOTA = $nama_kota;
                    $warehouse->NAMA_PROVINSI = $nama_provinsi;
                    $warehouse->NAMA_NEGARA = $nama_negara;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Warehouse::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $warehouses->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function json(Request $request)
    {
        // try {
        $query = Warehouse::query();
        $customer = new Warehouse(); // Create an instance of the Customer model

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Warehouse::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        // Apply pagination and limit
        $limit = $request->get('length', 10);
        $page = $request->get('start', 0) / $limit + 1;
        $skip = ($page - 1) * $limit;
        $total_data_after_search = $query->count();
        $customers = $query->take($limit)->skip($skip)->get();


        // ... rest of your code to enrich the data ...

        foreach ($customers as $customer) {
            $kota = City::where('KODE', $customer->KODE_KOTA)->first();
            $nama_kota = $kota->NAMA;
            $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
            $nama_provinsi = $provinsi->NAMA;
            $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
            $nama_negara = $negara->NAMA;

            $customer->NAMA_KOTA = $nama_kota;
            $customer->NAMA_PROVINSI = $nama_provinsi;
            $customer->NAMA_NEGARA = $nama_negara;
        }

        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Warehouse::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customers->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
    }
    public function findByKode($KODE)
    {
        try {
            $warehouse = Warehouse::where('KODE', $KODE)->first();
            //convert to array
            $warehouse = $warehouse->toArray();
            return ApiResponse::json(true, 'Warehouse retrieved successfully',  $warehouse);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve Warehouse', null, 500);
        }
    }


    public function index()
    {
        try {

            // $columns_shown = ['KODE', 'NAMA', 'NAMA_VENDOR', 'ALAMAT', 'NAMA_PIC', 'HP', 'EMAIL', 'FAX', 'NAMA_ACCOUNT'];

            //order by KODE
            $warehouses = Warehouse::orderBy('KODE')->get();
            foreach ($warehouses as $warehouse) {
                // $warehouse->NAMA_VENDOR = Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA;
                // $user_pic = Staff::where('KODE', $warehouse->KODE_PIC)->first();
                // $warehouse->NAMA_PIC = $user_pic->NAMA;
                // $warehouse->NAMA_ACCOUNT = Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA_ACCOUNT;


                // $warehouse->NO_HP = $user_pic->NO_HP;
                // $warehouse->EMAIL = $user_pic->EMAIL;

                $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
                $nama_kota = $kota->NAMA;
                $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
                $nama_provinsi = $provinsi->NAMA;
                $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
                $nama_negara = $negara->NAMA;

                $warehouse->NAMA_KOTA = $nama_kota;
                $warehouse->NAMA_PROVINSI = $nama_provinsi;
                $warehouse->NAMA_NEGARA = $nama_negara;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $warehouses);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:warehouses,NAMA',
            'JENIS_LOKASI' => 'required',
            // 'KODE_VENDOR' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            'KETERANGAN' => 'required',
            'NAMA_PIC' => 'required',
            'HP_PIC' => 'required',
            'EMAIL_PIC' => 'required',
            // 'FAX' => 'required',
            // 'KODE_ACCOUNT' => 'required',
        ], []);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $existingData = Warehouse::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($existingData) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }


        try {

            $new_Warehouse = new Warehouse();
            $new_Warehouse->KODE = $this->getLocalNextId();
            $new_Warehouse->NAMA = $request->NAMA;
            $new_Warehouse->JENIS_LOKASI = $request->JENIS_LOKASI;
            // $new_Warehouse->KODE_VENDOR = $request->KODE_VENDOR;
            $new_Warehouse->ALAMAT = $request->ALAMAT;
            $new_Warehouse->KODE_KOTA = $request->KODE_KOTA;
            $new_Warehouse->KETERANGAN = $request->KETERANGAN;
            $new_Warehouse->NAMA_PIC = $request->NAMA_PIC;
            $new_Warehouse->HP_PIC = $request->HP_PIC;
            $new_Warehouse->EMAIL_PIC = $request->EMAIL_PIC;

            // $new_Warehouse->FAX = $request->FAX;
            // $new_Warehouse->KODE_ACCOUNT = $request->KODE_ACCOUNT;




            $new_Warehouse->save();
            if (!$new_Warehouse) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_Warehouse->KODE;

            $warehouse = Warehouse::where('KODE', $id)->first();


            $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
            $nama_kota = $kota->NAMA;
            $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
            $nama_provinsi = $provinsi->NAMA;
            $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
            $nama_negara = $negara->NAMA;

            $warehouse->NAMA_KOTA = $nama_kota;
            $warehouse->NAMA_PROVINSI = $nama_provinsi;
            $warehouse->NAMA_NEGARA = $nama_negara;



            return ApiResponse::json(true, "Data inserted successfully with KODE $warehouse->KODE", $warehouse, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required|unique:warehouses,NAMA,' . $KODE . ',KODE',
            'JENIS_LOKASI' => 'required',
            // 'KODE_VENDOR' => 'required',
            'ALAMAT' => 'required',
            'KODE_KOTA' => 'required',
            'KETERANGAN' => 'required',
            'NAMA_PIC' => 'required',
            'HP_PIC' => 'required',
            'EMAIL_PIC' => 'required',
            // 'FAX' => 'required',
            // 'KODE_ACCOUNT' => 'required',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $existingData = Warehouse::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {

            $warehouse = Warehouse::findOrFail($KODE);

            $warehouse->NAMA = $request->NAMA;
            $warehouse->JENIS_LOKASI = $request->JENIS_LOKASI;
            // $warehouse->KODE_VENDOR = $request->KODE_VENDOR;
            $warehouse->ALAMAT = $request->ALAMAT;
            $warehouse->KODE_KOTA = $request->KODE_KOTA;
            $warehouse->KETERANGAN = $request->KETERANGAN;
            $warehouse->NAMA_PIC = $request->NAMA_PIC;
            $warehouse->HP_PIC = $request->HP_PIC;
            $warehouse->EMAIL_PIC = $request->EMAIL_PIC;
            // $warehouse->FAX = $request->FAX;
            // $warehouse->KODE_ACCOUNT = $request->KODE_ACCOUNT;



            $warehouse->save();
            if (!$warehouse) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            // $resp_Warehouse = array(
            //     'KODE' => $warehouse->KODE,
            //     'NAMA' => $warehouse->NAMA,
            //     // 'NAMA_VENDOR' => Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA,
            //     'ALAMAT' => $warehouse->ALAMAT,
            //     'NAMA_PIC' => Staff::where('KODE', $warehouse->KODE_PIC)->first()->NAMA,
            //     // 'FAX' => $warehouse->FAX,
            //     // 'NAMA_ACCOUNT' => Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA,




            // );


            $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
            $nama_kota = $kota->NAMA;
            $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
            $nama_provinsi = $provinsi->NAMA;
            $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
            $nama_negara = $negara->NAMA;

            $warehouse->NAMA_KOTA = $nama_kota;
            $warehouse->NAMA_PROVINSI = $nama_provinsi;
            $warehouse->NAMA_NEGARA = $nama_negara;
            return ApiResponse::json(true, 'Warehouse successfully updated', $warehouse, 200);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $warehouse = Warehouse::findOrFail($KODE);
            $warehouse->delete();
            return ApiResponse::json(true, 'Warehouse successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete Warehouse', null, 500);
        }
    }

    public function trash()
    {
        $warehouses = Warehouse::onlyTrashed()->get();

        $resp_Warehouses = [];

        foreach ($warehouses as $warehouse) {
            // $resp_Warehouse = [
            //     'KODE' => $warehouse->KODE,
            //     'NAMA' => $warehouse->NAMA,
            //     // 'NAMA_VENDOR' => Vendor::where('KODE', $warehouse->KODE_VENDOR)->first()->NAMA,
            //     'ALAMAT' => $warehouse->ALAMAT,
            //     'NAMA_PIC' => Staff::where('KODE', $warehouse->KODE_PIC)->first()->NAMA,
            //     // 'FAX' => $warehouse->FAX,
            //     // 'NAMA_ACCOUNT' => Account::where('KODE', $warehouse->KODE_ACCOUNT)->first()->NAMA,



            // ];

            // $resp_Warehouses[] = $resp_Warehouse;


            $kota = City::where('KODE', $warehouse->KODE_KOTA)->first();
            $nama_kota = $kota->NAMA;
            $provinsi = Province::where('KODE', $kota->KODE_PROVINSI)->first();
            $nama_provinsi = $provinsi->NAMA;
            $negara = Country::where('KODE', $provinsi->KODE_NEGARA)->first();
            $nama_negara = $negara->NAMA;

            $warehouse->setAttribute('NAMA_KOTA', $nama_kota);
            $warehouse->setAttribute('NAMA_PROVINSI', $nama_provinsi);
            $warehouse->setAttribute('NAMA_NEGARA', $nama_negara);
        }

        return ApiResponse::json(true, 'Trash bin fetched', $warehouses);
    }





    public function restore($id)
    {
        $restored = Warehouse::onlyTrashed()->findOrFail($id);
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
        $maxId = Warehouse::where('KODE', 'LIKE', 'LOK.%')
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
        $nextId = 'LOK.' . $nextNumber;

        return $nextId;
    }
}
