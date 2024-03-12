<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\TruckRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TruckRouteController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = TruckRoute::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = TruckRoute::first()->getAttributes(); // Get all attribute names
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
                'city_from' => [
                    'table' => 'cities',
                    'column' => 'NAMA',
                ],
                'city_to' => [
                    'table' => 'cities',
                    'column' => 'NAMA',
                ]
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
            foreach ($allData as $truckroute) {
                $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
                $truckroute->NAMA_KOTA_ASAL = $city_asal->NAMA;
                $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
                $truckroute->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
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
        $truckroutes = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($truckroutes as $truckroute) {
                $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
                $truckroute->NAMA_KOTA_ASAL = $city_asal->NAMA;
                $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
                $truckroute->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => TruckRoute::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $truckroutes->values()->toArray(),
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
            3 => ['city_from', 'NAMA'],
            5 => ['city_to', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = TruckRoute::query();

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
                foreach ($allData as $truckroute) {
                    $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
                    $truckroute->NAMA_KOTA_ASAL = $city_asal->NAMA;
                    $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
                    $truckroute->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
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

            $truckroutes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($truckroutes as $truckroute) {
                    $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
                    $truckroute->NAMA_KOTA_ASAL = $city_asal->NAMA;
                    $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
                    $truckroute->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => TruckRoute::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $truckroutes->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function json(Request $request)
    {
        // try {
        $query = TruckRoute::query();
        $customer = new TruckRoute(); // Create an instance of the Customer model

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = TruckRoute::first()->getAttributes(); // Get all attribute names
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
            $city_asal = City::where('KODE', $customer->KD_KOTA_ASAL)->first();
            $customer->NAMA_KOTA_ASAL = $city_asal->NAMA;
            $city_tujuan = City::where('KODE', $customer->KD_KOTA_TUJUAN)->first();
            $customer->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
        }

        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => TruckRoute::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $customers->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
    }
    public function findByKode($KODE)
    {
        try {
            $truckroute = TruckRoute::where('KODE', $KODE)->first();
            //convert to array
            $truckroute = $truckroute->toArray();
            return ApiResponse::json(true, 'truckroute retrieved successfully',  $truckroute);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve truckroute', null, 500);
        }
    }

    public function getKota()
    {
        $kota = City::all();
        return response()->json([
            'kota' => $kota
        ]);
    }

    public function indexWeb()
    {
        try {
            //all truck routes order by KODE string character asc
            $truckroutes = TruckRoute::all()->sortBy('KODE');

            foreach ($truckroutes as $truckroute) {
                $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
                $truckroute->NAMA_KOTA_ASAL = $city_asal->NAMA;
                $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
                $truckroute->NAMA_KOTA_TUJUAN = $city_tujuan->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $truckroutes);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            //kode string with format XXX-XXX
            // 'KODE' => 'required|string|unique:truck_routes,KODE|regex:/^[A-Z]{3}-[A-Z]{3}$/i',

            'RUTE_ASAL' => 'required',
            'KD_KOTA_ASAL' => 'required',
            'RUTE_TUJUAN' => 'required',
            'KD_KOTA_TUJUAN' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'KODE.regex' => 'KODE must be in format XXX-XXX (UPPERCASE), ex: ABC-DEF',

            'KD_KOTA_ASAL.required' => 'KD_KOTA_ASAL is required!',
            'KD_KOTA_TUJUAN.required' => 'KD_KOTA_TUJUAN is required!',
            'KETERANGAN.required' => 'KETERANGAN is required!',
        ]);




        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        // $truckroute = TruckRoute::where(DB::raw('LOWER("RUTE")'), strtolower($request->RUTE))->first();
        // if ($truckroute) {
        //     return ApiResponse::json(false, ['RUTE' => ['The RUTE has already been taken.']], null, 422);
        // }
        // try {
        $validatedData = $validator->validated();
        $KD_KOTA_ASAL = $validatedData['KD_KOTA_ASAL'];
        $KD_KOTA_TUJUAN = $validatedData['KD_KOTA_TUJUAN'];


        $new_truckroute = new TruckRoute();
        // $new_truckroute->KODE = TruckRoute::withTrashed()->max('KODE') + 1;
        $new_truckroute->KODE = $this->getLocalNextId();
        $new_truckroute->RUTE_ASAL = $request->get('RUTE_ASAL');
        $new_truckroute->KD_KOTA_ASAL = $KD_KOTA_ASAL;
        $new_truckroute->RUTE_TUJUAN = $request->get('RUTE_TUJUAN');
        $new_truckroute->KD_KOTA_TUJUAN = $KD_KOTA_TUJUAN;

        $new_truckroute->KETERANGAN = $validatedData['KETERANGAN'];
        $new_truckroute->save();
        if (!$new_truckroute) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }

        $resp_truckrouter = array(
            'KODE' => $new_truckroute->KODE,

            'RUTE_ASAL' => $new_truckroute->RUTE_ASAL,
            'NAMA_KOTA_ASAL' => City::where('KODE', $new_truckroute->KD_KOTA_ASAL)->first()->NAMA,
            'RUTE_TUJUAN' => $new_truckroute->RUTE_TUJUAN,
            'NAMA_KOTA_TUJUAN' => City::where('KODE', $new_truckroute->KD_KOTA_TUJUAN)->first()->NAMA,

            'KETERANGAN' => $new_truckroute->KETERANGAN,

        );


        return ApiResponse::json(true, "Data inserted successfully with KODE $new_truckroute->KODE", $resp_truckrouter, 201);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            // 'KODE' => 'required|string|unique:truck_routes,KODE,' . $KODE . ',KODE|regex:/^[A-Z]{3}-[A-Z]{3}$/i',

            'RUTE_ASAL' => 'required',
            'KD_KOTA_ASAL' => 'required',
            'RUTE_TUJUAN' => 'required',
            'KD_KOTA_TUJUAN' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'KODE.regex' => 'KODE must be in format XXX-XXX (UPPERCASE), ex: ABC-DEF',

            'KD_KOTA_ASAL.required' => 'KD_KOTA_ASAL is required!',
            'KD_KOTA_TUJUAN.required' => 'KD_KOTA_TUJUAN is required!',
            'KETERANGAN.required' => 'KETERANGAN is required!',
        ]);



        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $truckroute = TruckRoute::where(DB::raw('LOWER("RUTE")'), strtolower($request->RUTE))->where('KODE', '!=', $KODE)->first();
        // if ($truckroute) {
        //     return ApiResponse::json(false, ['RUTE' => ['The RUTE has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();
            $KD_KOTA_ASAL = $validatedData['KD_KOTA_ASAL'];
            $KD_KOTA_TUJUAN = $validatedData['KD_KOTA_TUJUAN'];



            $truckroute = TruckRoute::findOrFail($KODE);

            if ($truckroute->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update truck route because it has related records', null, 400);
            }

            // $truckroute->KODE = $validatedData['KODE'];
            $truckroute->RUTE_ASAL = $request->get('RUTE_ASAL');
            $truckroute->KD_KOTA_ASAL = $request->get('KD_KOTA_ASAL');
            $truckroute->RUTE_TUJUAN = $request->get('RUTE_TUJUAN');
            $truckroute->KD_KOTA_TUJUAN = $request->get('KD_KOTA_TUJUAN');

            $truckroute->KETERANGAN = $request->get('KETERANGAN');
            $truckroute->save();
            if (!$truckroute) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            $resp_truckroute = array(
                'KODE' => $truckroute->KODE,

                'RUTE_ASAL' => $truckroute->RUTE_ASAL,
                'NAMA_KOTA_ASAL' => City::where('KODE', $truckroute->KD_KOTA_ASAL)->first()->NAMA,
                'RUTE_TUJUAN' => $truckroute->RUTE_TUJUAN,
                'NAMA_KOTA_TUJUAN' => City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first()->NAMA,
                'KETERANGAN' => $truckroute->KETERANGAN,
            );
            return ApiResponse::json(true, 'truckroute successfully updated', $resp_truckroute);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = TruckRoute::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'truckroute successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $provinces = TruckRoute::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $truckroute) {
            $city_asal = City::where('KODE', $truckroute->KD_KOTA_ASAL)->first();
            $city_tujuan = City::where('KODE', $truckroute->KD_KOTA_TUJUAN)->first();
            // get the province based on the city

            $resp_province = [
                'KODE' => $truckroute->KODE,

                'RUTE_ASAL' => $truckroute->RUTE_ASAL,
                'NAMA_KOTA_ASAL' => $city_asal ? $city_asal->NAMA : null,
                'RUTE_TUJUAN' => $truckroute->RUTE_TUJUAN,
                'NAMA_KOTA_TUJUAN' => $city_tujuan ? $city_tujuan->NAMA : null,
                'KETERANGAN' => $truckroute->KETERANGAN,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = TruckRoute::onlyTrashed()->findOrFail($id);
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
        $maxId = TruckRoute::where('KODE', 'LIKE', 'RT.%')
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
        $nextId = 'RT.' . $nextNumber;

        return $nextId;
    }
}
