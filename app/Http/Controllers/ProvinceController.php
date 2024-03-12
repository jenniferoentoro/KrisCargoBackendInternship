<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProvinceController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Province::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Province::first()->getAttributes(); // Get all attribute names
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
                'country' => [
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
            foreach ($allData as $province) {
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $province->NAMA_NEGARA = $country ? $country->NAMA : null;
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
        $provinces = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($provinces as $province) {
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $province->NAMA_NEGARA = $country ? $country->NAMA : null;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Province::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $provinces->values()->toArray(),
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
            3 => ['country', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Province::query();

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
                foreach ($allData as $province) {
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $province->NAMA_NEGARA = $country ? $country->NAMA : null;
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

            $provinces = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($provinces as $province) {
                    $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                    $province->NAMA_NEGARA = $country ? $country->NAMA : null;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Province::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $provinces->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }


    public function findByKodeNegara($KODE)
    {
        try {
            $province = Province::where('KODE_NEGARA', $KODE)->get();
            return ApiResponse::json(true, 'Provinces retrieved successfully',  $province);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve provinces', null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $province = Province::where('KODE', $KODE)->first();
            //convert to array
            $province = $province->toArray();
            return ApiResponse::json(true, 'Province retrieved successfully',  $province);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve province', null, 500);
        }
    }

    public function indexWeb()
    {
        try {
            //get provinces oorder by KODE
            $provinces = Province::orderBy('KODE')->get();
            // Fetch the country name for each province
            foreach ($provinces as $province) {
                $country = Country::where('KODE', $province->KODE_NEGARA)->first();
                $province->NAMA_NEGARA = $country ? $country->NAMA : null;
            }

            return ApiResponse::json(true, 'Data retrieved successfully',  $provinces);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }
    public function index()
    {
        try {
            //get provinces oorder by KODE
            $provinces = Province::orderBy('KODE')->get();
            // Fetch the country name for each province
            // foreach ($provinces as $province) {
            //     $country = Country::where('KODE', $province->KODE_NEGARA)->first();
            //     $province->NAMA_NEGARA = $country ? $country->NAMA : null;
            // }

            return ApiResponse::json(true, 'Data retrieved successfully',  $provinces);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }


    public function storeWeb(Request $request)
    {
        // Make validator here
        $validator = Validator::make($request->all(), [
            'KODE_NEGARA' => 'required',
            'NAMA' => 'required|unique:provinces,NAMA',
        ], [
            'KODE_NEGARA.required' => 'The KODE_NEGARA field is required.',
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // check uniqueness for NAMA case insensitive
        $province = Province::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($province) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }

        try {
            $validatedData = $validator->validated();
            $kode_negara = $validatedData['KODE_NEGARA'];
            $country = Country::where('KODE', $kode_negara)->first();

            if (!$country) {
                return ApiResponse::json(false, 'Invalid KODE_NEGARA', null, 400);
            }

            $new_province = new Province();
            $new_province->KODE = $this->getLocalNextId();
            $new_province->KODE_NEGARA = $kode_negara;
            $new_province->NAMA = $validatedData['NAMA'];
            $new_province->save();

            if (!$new_province) {
                return ApiResponse::json(false, 'Data not inserted', null, 404);
            }
            $id = $new_province->KODE;

            $province = Province::where('KODE', $id)->first();
            $resp_province = array(
                'KODE' => $province->KODE,
                'NAMA' => $province->NAMA,
                'NAMA_NEGARA' => Country::where('KODE', $province->KODE_NEGARA)->first()->NAMA,

            );

            return ApiResponse::json(true, "Data inserted successfully with KODE $province->KODE", $resp_province, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }



    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'KODE_NEGARA' => 'required',
            'NAMA' => 'required|unique:provinces,NAMA,' . $KODE . ',KODE',
        ], [
            'KODE_NEGARA.required' => 'The KODE_NEGARA field is required.',
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        // Check uniqueness for NAMA case insensitive except for current KODE
        $province = Province::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($province) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();
            $kode_negara = $validatedData['KODE_NEGARA'];
            $country = Country::where('KODE', $kode_negara)->first();

            if (!$country) {
                return ApiResponse::json(false, 'Invalid KODE_NEGARA', null, 400);
            }
            $province = Province::findOrFail($KODE);

            //check if hasrelatedrecords()
            if ($province->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update province because it has related records', null, 400);
            }

            $province->KODE_NEGARA = $kode_negara;
            $province->NAMA = $validatedData['NAMA'];
            $province->save();
            if (!$province) {
                return ApiResponse::json(false, 'Data not updated', null, 404);
            }
            $resp_province = array(
                'KODE' => $province->KODE,
                'NAMA' => $province->NAMA,
                'NAMA_NEGARA' => Country::where('KODE', $province->KODE_NEGARA)->first()->NAMA,

            );
            return ApiResponse::json(true, 'Province successfully updated', $resp_province, 200);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update province', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $province = Province::findOrFail($KODE);
            $province->delete();
            return ApiResponse::json(true, 'Province successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $provinces = Province::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $province) {
            $resp_province = [
                'KODE' => $province->KODE,
                'NAMA' => $province->NAMA,
                'NAMA_NEGARA' => Country::where('KODE', $province->KODE_NEGARA)->first()->NAMA,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }


    public function restore($id)
    {
        $restored = Province::onlyTrashed()->findOrFail($id);
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
        $maxId = Province::where('KODE', 'LIKE', 'PRV.%')
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
        $nextId = 'PRV.' . $nextNumber;

        return $nextId;
    }
}
