<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\CostType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CostTypeController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = CostType::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = CostType::first()->getAttributes(); // Get all attribute names
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

        $query->orderBy('KODE', 'ASC');


        $allData = $query->get();



        if (!empty($columnToSort)) {

            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return $data->KODE;
                }, SORT_STRING, $sortBoolean);
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
        $costtypes = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => CostType::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $costtypes->values()->toArray(),
        ];



        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function dataTableAdvJson(Request $request)
    {
        //related to function
        $relationColumnsAndTo = [];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = CostType::query();

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

            $query->orderBy('KODE', 'ASC');

            $allData = $query->get();



            if (!empty($columnToSort)) {

                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return $data->KODE;
                    }, SORT_STRING, $sortBoolean);
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

            $costtypes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => CostType::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $costtypes->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }


    public function findByKode($KODE)
    {
        try {
            $User = CostType::where('KODE', $KODE)->first();
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

    public function index()
    {
        try {
            //get all Cost Type order by KODE
            $costType = CostType::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $costType);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // kode uniquue
            'KODE' => 'required|unique:cost_types,KODE',
            'NAMA' => 'required|unique:cost_types,NAMA',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        //check uniqueness for KODE case insensitive
        $costType = CostType::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->first();
        if ($costType) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Cost Type has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $costType = CostType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($costType) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_costType = new CostType();
            $new_costType->KODE = $validatedData['KODE'];
            $new_costType->NAMA = $validatedData['NAMA'];
            $new_costType->save();



            if (!$new_costType) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $new_costType->KODE,
                'NAMA' => $new_costType->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_costType->KODE", $resp_containertype, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            // kode uniquue
            'KODE' => 'required|unique:cost_types,KODE,' . $KODE . ',KODE',
            'NAMA' => 'required|unique:cost_types,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        //check uniqueness for KODE case insensitive except for current KODE
        $costType = CostType::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->where('KODE', '!=', $KODE)->first();
        if ($costType) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Cost Type has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $costType = CostType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($costType) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $costType = CostType::findOrFail($KODE);
            if ($costType->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update cost Type because it has related records', null, 400);
            }
            $costType->KODE = $validatedData['KODE'];
            $costType->NAMA = $validatedData['NAMA'];
            $costType->save();
            if (!$costType) {
                return ApiResponse::json(false, 'Failed to update Cost Type', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $costType->KODE,
                'NAMA' => $costType->NAMA,
            );
            return ApiResponse::json(true, 'Cost Type successfully updated', $resp_containertype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Cost Type', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $costType = CostType::findOrFail($KODE);
            $costType->delete();
            return ApiResponse::json(true, 'Cost Type successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {

        $costTypes = CostType::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $costTypes);
    }

    public function restore($id)
    {
        $restored = CostType::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = CostType::onlyTrashed()->findOrFail($id);

        if ($post->cover) {
            Storage::delete('public/' . $post->cover);
        }

        $post->forceDelete();

        return to_route('posts.trash')->with('success', 'Post deleted permanently');
    }



    public function getNextId()
    {
        // $tableName = 'cost_types'; // Replace with your table name
        // $autoIncrementColumn = 'KODE'; // Replace with your auto increment column name
        // $sequenceName = $tableName . '_' . $autoIncrementColumn . '_seq';

        // $query = DB::raw("SELECT nextval('\"$sequenceName\"') AS next_id");

        // $result = DB::select($query);

        // $nextId = $result[0]->next_id;

        // return ApiResponse::json(true, 'Data retrieved successfully',  $nextId + 1);


        //get max KODE despite deleted_at
        $maxKODE = CostType::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
