<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ContainerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContainerTypeController extends Controller
{
    public function dataTableJson(Request $request)
    {
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];


            $query = ContainerType::query();

            $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
            // Apply search filter if applicable
            if ($hasSearch) {
                $searchValue = strtoupper($request->input('search')['value']);
                $attributes = ContainerType::first()->getAttributes(); // Get all attribute names
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

            $query->orderByRaw('CAST(SUBSTRING("KODE" FROM \'\\.([0-9]+)$\') AS INTEGER) ASC, "KODE" ASC'); // Order by the numeric part of KODE


            $allData = $query->get();



            if (!empty($columnToSort)) {

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

            $containerTypes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }

            // ... (rest of your code to enrich the data)

            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => ContainerType::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $containerTypes->values()->toArray(),
            ];



            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
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

            $query = ContainerType::query();

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

            $containerTypes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }



            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => ContainerType::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $containerTypes->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $User = ContainerType::where('KODE', $KODE)->first();
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
            //get all Container Type order by KODE
            $containertype = ContainerType::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $containertype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'KODE' => 'required|unique:container_types,KODE',
            'NAMA' => 'required|unique:container_types,NAMA',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // check uniquness for KODE case insensitive
        $containertype = ContainerType::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->first();
        if ($containertype) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Container Type has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $containertype = ContainerType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($containertype) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_containertype = new ContainerType();
            $new_containertype->KODE = $validatedData['KODE'];
            $new_containertype->NAMA = $validatedData['NAMA'];
            $new_containertype->save();



            if (!$new_containertype) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $new_containertype->KODE,
                'NAMA' => $new_containertype->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_containertype->KODE", $resp_containertype, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            // unique and required except for current KODE
            'KODE' => 'required|unique:container_types,KODE,' . $KODE . ',KODE',
            'NAMA' => 'required|unique:container_types,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // check uniquness for KODE case insensitive except for current KODE
        $containertype = ContainerType::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->where('KODE', '!=', $KODE)->first();
        if ($containertype) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Container Type has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $containertype = ContainerType::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($containertype) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $containertype = ContainerType::findOrFail($KODE);
            $containertype->KODE = $validatedData['KODE'];
            $containertype->NAMA = $validatedData['NAMA'];
            $containertype->save();
            if (!$containertype) {
                return ApiResponse::json(false, 'Failed to update Container Type', null, 500);
            }
            $resp_containertype = array(
                'KODE' => $containertype->KODE,
                'NAMA' => $containertype->NAMA,
            );
            return ApiResponse::json(true, 'Container Type successfully updated', $resp_containertype);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Container Type', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $containertype = ContainerType::findOrFail($KODE);
            $containertype->delete();
            return ApiResponse::json(true, 'Container Type successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete Container Type', null, 500);
        }
    }

    public function trash()
    {

        $containertypes = ContainerType::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $containertypes);
    }

    public function restore($id)
    {
        $restored = ContainerType::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = ContainerType::onlyTrashed()->findOrFail($id);

        if ($post->cover) {
            Storage::delete('public/' . $post->cover);
        }

        $post->forceDelete();

        return to_route('posts.trash')->with('success', 'Post deleted permanently');
    }



    public function getNextId()
    {
        // $tableName = 'container_types'; // Replace with your table name
        // $autoIncrementColumn = 'KODE'; // Replace with your auto increment column name
        // $sequenceName = $tableName . '_' . $autoIncrementColumn . '_seq';

        // $query = DB::raw("SELECT nextval('\"$sequenceName\"') AS next_id");

        // $result = DB::select($query);

        // $nextId = $result[0]->next_id;

        // return ApiResponse::json(true, 'Data retrieved successfully',  $nextId + 1);


        //get max KODE despite deleted_at
        $maxKODE = ContainerType::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
