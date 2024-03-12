<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    // public function json(Request $request)
    // {
    //     // try {
    //     $query = City::query();
    //     $customer = new City(); // Create an instance of the Customer model

    //     $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
    //     // Apply search filter if applicable
    //     if ($hasSearch) {
    //         $searchValue = strtoupper($request->input('search')['value']);
    //         $attributes = City::first()->getAttributes(); // Get all attribute names
    //         $query->where(function ($q) use ($searchValue, $attributes) {
    //             foreach ($attributes as $attribute => $value) {
    //                 $q->orWhere($attribute, 'LIKE', "%$searchValue%");
    //             }
    //         });

    //         $relatedColumns = [

    //             'province' => [
    //                 'table' => 'provinces',
    //                 'column' => 'NAMA',
    //             ],


    //         ];

    //         foreach ($relatedColumns as $relation => $relatedColumn) {
    //             $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
    //                 $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
    //             });
    //         }
    //     }

    //     // Apply pagination and limit
    //     $limit = $request->get('length', 10);
    //     $page = $request->get('start', 0) / $limit + 1;
    //     $skip = ($page - 1) * $limit;
    //     $total_data_after_search = $query->count();
    //     $customers = $query->take($limit)->skip($skip)->get();


    //     // ... rest of your code to enrich the data ...

    //     foreach ($customers as $customer) {
    //         $province = Province::where('KODE', $customer->KODE_PROVINSI)->first();
    //         $customer->NAMA_PROVINSI = $province ? $province->NAMA : null;
    //     }

    //     // Prepare the response data structure expected by the frontend

    //     $response = [
    //         'draw' => $request->input('draw'),
    //         'recordsTotal' => City::count(),
    //         'recordsFiltered' => $total_data_after_search,
    //         'data' => $customers->toArray(),
    //     ];



    //     return ApiResponse::json(true, 'Data retrieved successfully', $response);
    // }

    public function dataTableJson(Request $request)
    {
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];


            $query = City::query();

            $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
            // Apply search filter if applicable
            if ($hasSearch) {
                $searchValue = strtoupper($request->input('search')['value']);
                $attributes = City::first()->getAttributes(); // Get all attribute names
                $query->where(function ($q) use ($searchValue, $attributes) {
                    foreach ($attributes as $attribute => $value) {
                        $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                    }
                });

                $relatedColumns = [

                    'province' => [
                        'table' => 'provinces',
                        'column' => 'NAMA',
                    ],
                ];

                foreach ($relatedColumns as $relation => $relatedColumn) {
                    $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                        $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                    });
                }
            }

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC'); // Order by the numeric part of KODE


            $allData = $query->get();



            // if (!empty($columnToSort)) {
            //     if ($sortDir === 'ASC') {
            //         $allData = $allData->sortBy($columnToSort);
            //     } else {
            //         $allData = $allData->sortByDesc($columnToSort);
            //     }
            // }

            // $limit = $request->get('length', 10);

            // // Apply pagination and limit
            // $limit = $request->get('length', 10);
            // $page = $request->get('start', 0) / $limit + 1;
            // $skip = ($page - 1) * $limit;
            // $total_data_after_search = $query->count();
            // $cities = $allData->slice($skip, $limit);


            // // ... rest of your code to enrich the data ...



            // // Sort the cities after the data enrichment
            // //if columnsToSort is not null or empty

            // // return ApiResponse::json(true, 'Data retrieved successfully', $cities);

            // // Prepare the response data structure expected by the frontend

            // $response = [
            //     'draw' => $request->input('draw'),
            //     'recordsTotal' => City::count(),
            //     'recordsFiltered' => $total_data_after_search,
            //     'data' => $cities->toArray(),
            // ];

            if (!empty($columnToSort)) {
                foreach ($allData as $city) {
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $city->NAMA_PROVINSI = $province ? $province->NAMA : null;
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

            // Apply pagination and limit to the sorted and converted data
            $limit = $request->get('length', 10);
            $total_data_after_search = $query->count();
            if ($limit == -1) {
                $limit = $total_data_after_search; // Use the total count after search filters
            }
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;

            $cities = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($cities as $city) {
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $city->NAMA_PROVINSI = $province ? $province->NAMA : null;
                }
            }

            // ... (rest of your code to enrich the data)

            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => City::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $cities->values()->toArray(),
            ];



            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [
            3 => ['province', 'NAMA'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = City::query();

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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC'); // Order by the numeric part of KODE

            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $city) {
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $city->NAMA_PROVINSI = $province ? $province->NAMA : null;
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

            $cities = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($cities as $city) {
                    $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                    $city->NAMA_PROVINSI = $province ? $province->NAMA : null;
                }
            }



            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => City::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $cities->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $User = City::where('KODE', $KODE)->first();
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
    public function findByKodeProvince($KODE)
    {
        try {
            $cities = City::where('KODE_PROVINSI', $KODE)->get();
            return ApiResponse::json(true, 'Cities retrieved successfully',  $cities);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve cities', null, 500);
        }
    }

    public function getCityByKode($KODE)
    {
        try {
            $city = City::where('KODE', $KODE)->first();
            if (!$city) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            $city->NAMA_PROVINSI = Province::where('KODE', $city->KODE_PROVINSI)->first()->NAMA;
            return ApiResponse::json(true, 'Data retrieved successfully', $city);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function dropdown()
    {
        //only get KODE and NAMA
        try {
            $cities = City::select('KODE', 'NAMA')->orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $cities);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function indexWeb()
    {
        try {
            //order by KODE
            $cities = City::orderBy('KODE')->get();
            foreach ($cities as $city) {
                $province = Province::where('KODE', $city->KODE_PROVINSI)->first();
                $city->NAMA_PROVINSI = $province ? $province->NAMA : null;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $cities);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function index()
    {
        try {
            //order by KODE
            $cities = City::orderBy('KODE')->get();

            return ApiResponse::json(true, 'Data retrieved successfully',  $cities);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_PROVINSI' => 'required',
            'NAMA' => 'required|unique:cities,NAMA',
        ], [
            'KODE_PROVINSI.required' => 'The KODE_PROVINSI field is required.',
            'NAMA.required' => 'The NAMA field is required.',
        ]);


        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $city = City::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($city) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }


        try {
            $validatedData = $validator->validated();
            $KODE_PROVINSI = $validatedData['KODE_PROVINSI'];
            $province = Province::where('KODE', $KODE_PROVINSI)->first();

            if (!$province) {
                return ApiResponse::json(false, 'Invalid KODE_PROVINSI', null, 400);
            }




            $new_city = new City();
            $new_city->KODE = $this->getLocalNextId();
            $new_city->KODE_PROVINSI = $KODE_PROVINSI;
            $new_city->NAMA = $validatedData['NAMA'];
            $new_city->save();
            if (!$new_city) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_city->KODE;

            $city = City::where('KODE', $id)->first();
            $resp_city = array(
                'KODE' => $city->KODE,
                'NAMA' => $city->NAMA,
                'NAMA_PROVINSI' => Province::where('KODE', $city->KODE_PROVINSI)->first()->NAMA,

            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $city->KODE", $resp_city, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_PROVINSI' => 'required',
            'NAMA' => 'required|unique:cities,NAMA,' . $KODE . ',KODE',
        ], [
            'KODE_PROVINSI.required' => 'The KODE_PROVINSI field is required.',
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $city = City::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($city) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();
            $KODE_PROVINSI = $validatedData['KODE_PROVINSI'];
            $province = Province::where('KODE', $KODE_PROVINSI)->first();

            if (!$province) {
                return ApiResponse::json(false, 'Invalid KODE_PROVINSI', null, 400);
            }
            $city = City::findOrFail($KODE);
            if ($city->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update city because it has related records', null, 400);
            }
            $city->KODE_PROVINSI = $request->get('KODE_PROVINSI');
            $city->NAMA = $request->get('NAMA');
            $city->save();
            if (!$city) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            $resp_city = array(
                'KODE' => $city->KODE,
                'NAMA' => $city->NAMA,
                'NAMA_PROVINSI' => Province::where('KODE', $city->KODE_PROVINSI)->first()->NAMA,
            );
            return ApiResponse::json(true, 'City successfully updated', $resp_city);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = City::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'City successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $provinces = City::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $province) {
            $resp_province = [
                'KODE' => $province->KODE,
                'NAMA' => $province->NAMA,
                'NAMA_PROVINSI' => Province::where('KODE', $province->KODE_PROVINSI)->first()->NAMA,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }





    public function restore($id)
    {
        $restored = City::onlyTrashed()->findOrFail($id);
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
        $maxId = City::where('KODE', 'LIKE', 'KOT.%')
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
        $nextId = 'KOT.' . $nextNumber;

        return $nextId;
    }
}
