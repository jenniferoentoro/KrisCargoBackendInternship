<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SizeController extends Controller
{


    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Size::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Size::first()->getAttributes(); // Get all attribute names
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
        $sizes = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Size::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $sizes->values()->toArray(),
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

            $query = Size::query();

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

            $sizes = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Size::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $sizes->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $sizeType = Size::where('KODE', $KODE)->first();
            //convert to array
            $sizeType = $sizeType->toArray();
            return ApiResponse::json(true, 'Size retrieved successfully',  $sizeType);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve Size', null, 500);
        }
    }

    public function index()
    {
        try {
            //get all negara order by KODE
            $sizeType = Size::orderBy('KODE')->get();
            return ApiResponse::json(true, 'Data retrieved successfully',  $sizeType);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'KODE' => 'required|unique:sizes,KODE',
            'NAMA' => 'required|unique:sizes,NAMA',
        ], [
            'NAMA.required' => 'The Size field is required.',
            'NAMA.unique' => 'The Size has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // check uniqueness for KODE case insensitive
        $sizeType = Size::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->first();
        if ($sizeType) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Size has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $sizeType = Size::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($sizeType) {
            return ApiResponse::json(false, ['NAMA' => ['The Size has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_size = new Size();
            $new_size->KODE = $validatedData['KODE'];
            $new_size->NAMA = $validatedData['NAMA'];
            $new_size->save();



            if (!$new_size) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_vendor_type = array(
                'KODE' => $new_size->KODE,
                'NAMA' => $new_size->NAMA,
            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_size->KODE", $resp_vendor_type, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'KODE' => 'required|unique:sizes,KODE,' . $KODE . ',KODE',
            'NAMA' => 'required|unique:sizes,NAMA,' . $KODE . ',KODE',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // check uniqueness for KODE case insensitive except for current KODE
        $sizeType = Size::where(DB::raw('LOWER("KODE")'), strtolower($request->KODE))->where('KODE', '!=', $KODE)->first();
        if ($sizeType) {
            return ApiResponse::json(false, ['KODE' => ['The KODE Size has already been taken.']], null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $sizeType = Size::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($sizeType) {
            return ApiResponse::json(false, ['NAMA' => ['The Size has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();
            $sizeTypee = Size::findOrFail($KODE);
            if ($sizeTypee->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update size because it has related records', null, 400);
            }
            $sizeTypee->KODE = $validatedData['KODE'];
            $sizeTypee->NAMA = $validatedData['NAMA'];
            $sizeTypee->save();
            if (!$sizeTypee) {
                return ApiResponse::json(false, 'Failed to update Size', null, 500);
            }
            $resp_vendortypee = array(
                'KODE' => $sizeTypee->KODE,
                'NAMA' => $sizeTypee->NAMA,
            );
            return ApiResponse::json(true, 'Size successfully updated', $resp_vendortypee);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to update Size', null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $negara = Size::findOrFail($KODE);
            $negara->delete();
            return ApiResponse::json(true, 'Size successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {

        $negaras = Size::onlyTrashed()->get();

        return ApiResponse::json(true, 'Trash bin fetched',  $negaras);
    }

    public function restore($id)
    {
        $restored = Size::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function delete($id)
    {
        $post = Size::onlyTrashed()->findOrFail($id);

        if ($post->cover) {
            Storage::delete('public/' . $post->cover);
        }

        $post->forceDelete();

        return to_route('posts.trash')->with('success', 'Post deleted permanently');
    }



    public function getNextId()
    {
        // $tableName = 'sizes'; // Replace with your table name
        // $autoIncrementColumn = 'KODE'; // Replace with your auto increment column name
        // $sequenceName = $tableName . '_' . $autoIncrementColumn . '_seq';

        // $query = DB::raw("SELECT nextval('\"$sequenceName\"') AS next_id");

        // $result = DB::select($query);

        // $nextId = $result[0]->next_id;

        // return ApiResponse::json(true, 'Data retrieved successfully',  $nextId + 1);


        //get max KODE despite deleted_at
        $maxKODE = Size::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}