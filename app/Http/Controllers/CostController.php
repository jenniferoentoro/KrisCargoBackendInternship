<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Account;
use App\Models\City;
use App\Models\CostGroup;
use App\Models\CostType;
use App\Models\Cost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CostController extends Controller
{
    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = Cost::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = Cost::first()->getAttributes(); // Get all attribute names
            $query->where(function ($q) use ($searchValue, $attributes) {
                foreach ($attributes as $attribute => $value) {
                    $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                }
            });

            $relatedColumns = [

                'costGroup' => [
                    'table' => 'cost_groups',
                    'column' => 'NAMA',
                ],
                'costType' => [
                    'table' => 'cost_types',
                    'column' => 'NAMA',
                ],
                'account' => [
                    'table' => 'accounts',
                    'column' => 'NAMA_ACCOUNT',
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
            foreach ($allData as $costs) {
                $kel_biaya = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first();
                $costs->NAMA_KEL_BIAYA = $kel_biaya->NAMA;
                $JEN_biaya = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first();
                $costs->NAMA_JEN_BIAYA = $JEN_biaya->NAMA;

                $acc = Account::where('KODE', $costs->ACC)->first();
                $costs->NAMA_ACC = $acc->NAMA_ACCOUNT;
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
        $costss = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($costss as $costs) {
                $kel_biaya = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first();
                $costs->NAMA_KEL_BIAYA = $kel_biaya->NAMA;
                $JEN_biaya = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first();
                $costs->NAMA_JEN_BIAYA = $JEN_biaya->NAMA;

                $acc = Account::where('KODE', $costs->ACC)->first();
                $costs->NAMA_ACC = $acc->NAMA_ACCOUNT;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Cost::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $costss->values()->toArray(),
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
            3 => ['costGroup', 'NAMA'],
            4 => ['costType', 'NAMA'],
            5 => ['account', 'NAMA_ACCOUNT'],
        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = Cost::query();

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
                foreach ($allData as $costs) {
                    $kel_biaya = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first();
                    $costs->NAMA_KEL_BIAYA = $kel_biaya->NAMA;
                    $JEN_biaya = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first();
                    $costs->NAMA_JEN_BIAYA = $JEN_biaya->NAMA;

                    $acc = Account::where('KODE', $costs->ACC)->first();
                    $costs->NAMA_ACC = $acc->NAMA_ACCOUNT;
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

            $costss = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($costss as $costs) {
                    $kel_biaya = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first();
                    $costs->NAMA_KEL_BIAYA = $kel_biaya->NAMA;
                    $JEN_biaya = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first();
                    $costs->NAMA_JEN_BIAYA = $JEN_biaya->NAMA;

                    $acc = Account::where('KODE', $costs->ACC)->first();
                    $costs->NAMA_ACC = $acc->NAMA_ACCOUNT;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Cost::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $costss->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $User = Cost::where('KODE', $KODE)->first();
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

    public function indexWeb()
    {
        try {
            $costss = Cost::orderBy('KODE', 'asc')->get();

            foreach ($costss as $costs) {
                $kel_biaya = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first();
                $costs->NAMA_KEL_BIAYA = $kel_biaya->NAMA;
                $JEN_biaya = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first();
                $costs->NAMA_JEN_BIAYA = $JEN_biaya->NAMA;

                $acc = Account::where('KODE', $costs->ACC)->first();
                $costs->NAMA_ACC = $acc->NAMA_ACCOUNT;
            }
            return ApiResponse::json(true, 'Data retrieved successfully',  $costss);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'NAMA_BIAYA' => 'required|unique:costs,NAMA_BIAYA',
            'KD_KEL_BIAYA' => 'required',
            'KD_JEN_BIAYA' => 'required',
            'ACC' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'NAMA_BIAYA.required' => 'NAMA_BIAYA is required!',
            'KD_KEL_BIAYA.required' => 'KD_KEL_BIAYA is required!',
            'KD_JEN_BIAYA.required' => 'KD_JEN_BIAYA is required!',
            'ACC.required' => 'ACC is required!',
            'KETERANGAN.required' => 'KETERANGAN is required!',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $costs = Cost::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->first();
        if ($costs) {
            return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();


            $new_cost = new Cost();
            $new_cost->KODE = $this->getLocalNextId();
            $new_cost->KD_KEL_BIAYA = $validatedData['KD_KEL_BIAYA'];
            $new_cost->KD_JEN_BIAYA = $validatedData['KD_JEN_BIAYA'];
            $new_cost->NAMA_BIAYA = $validatedData['NAMA_BIAYA'];
            $new_cost->ACC = $validatedData['ACC'];
            $new_cost->KETERANGAN = $validatedData['KETERANGAN'];
            $new_cost->save();
            if (!$new_cost) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $new_cost->KODE;

            $costsr = Cost::where('KODE', $id)->first();

            $resp_costr = array(
                'KODE' => $costsr->KODE,

                'NAMA_KEL_BIAYA' => CostGroup::where('KODE', $costsr->KD_KEL_BIAYA)->first()->NAMA,
                'NAMA_JEN_BIAYA' => CostType::where('KODE', $costsr->KD_JEN_BIAYA)->first()->NAMA,

                'NAMA_BIAYA' => $costsr->NAMA_BIAYA,

                'NAMA_ACC' => Account::where('KODE', $costsr->ACC)->first()->NAMA_ACCOUNT,

                'KETERANGAN' => $costsr->KETERANGAN,

            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $costsr->KODE", $resp_costr, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [

            'NAMA_BIAYA' => 'required|unique:costs,NAMA_BIAYA,' . $KODE . ',KODE',
            'KD_KEL_BIAYA' => 'required',
            'KD_JEN_BIAYA' => 'required',
            'ACC' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'NAMA_BIAYA.required' => 'NAMA_BIAYA is required!',
            'KD_KEL_BIAYA.required' => 'KD_KEL_BIAYA is required!',
            'KD_JEN_BIAYA.required' => 'KD_JEN_BIAYA is required!',
            'ACC.required' => 'ACC is required!',
            'KETERANGAN.required' => 'KETERANGAN is required!',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        $costs = Cost::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        if ($costs) {
            return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        }
        try {
            $validatedData = $validator->validated();
            $KD_KEL_BIAYA = $validatedData['KD_KEL_BIAYA'];
            $KD_JEN_BIAYA = $validatedData['KD_JEN_BIAYA'];



            $costs = Cost::findOrFail($KODE);
            if ($costs->hasRelatedRecords()) {
                return ApiResponse::json(false, 'Cannot update cost because it has related records', null, 400);
            }
            $costs->KD_KEL_BIAYA = $request->get('KD_KEL_BIAYA');
            $costs->KD_JEN_BIAYA = $request->get('KD_JEN_BIAYA');
            $costs->NAMA_BIAYA = $request->get('NAMA_BIAYA');
            $costs->ACC = $request->get('ACC');
            $costs->KETERANGAN = $request->get('KETERANGAN');
            $costs->save();
            if (!$costs) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $resp_cost = array(
                'KODE' => $costs->KODE,
                'NAMA_BIAYA' => $costs->NAMA_BIAYA,
                'NAMA_KEL_BIAYA' => CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first()->NAMA,
                'NAMA_JEN_BIAYA' => CostType::where('KODE', $costs->KD_JEN_BIAYA)->first()->NAMA,
                'NAMA_ACC' => Account::where('KODE', $costs->ACC)->first()->NAMA_ACCOUNT,
                'KETERANGAN' => $costs->KETERANGAN,
            );
            return ApiResponse::json(true, 'cost successfully updated', $resp_cost);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = Cost::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'cost successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function trash()
    {
        $provinces = Cost::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $costs) {
            $NAMA_KEL_BIAYA = CostGroup::where('KODE', $costs->KD_KEL_BIAYA)->first()->NAMA;
            $NAMA_JEN_BIAYA = CostType::where('KODE', $costs->KD_JEN_BIAYA)->first()->NAMA;

            //check if acc is 1 or 0


            $resp_province = [
                'KODE' => $costs->KODE,
                'NAMA_BIAYA' => $costs->NAMA_BIAYA,
                'NAMA_KEL_BIAYA' => $NAMA_KEL_BIAYA,
                'NAMA_JEN_BIAYA' => $NAMA_JEN_BIAYA,
                'ACC' => Account::where('KODE,', $costs->ACC)->first()->NAMA_ACCOUNT,
                'KETERANGAN' => $costs->KETERANGAN,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = Cost::onlyTrashed()->findOrFail($id);
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
        $maxId = Cost::where('KODE', 'LIKE', 'NB.%')
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
        $nextId = 'NB.' . $nextNumber;

        return $nextId;
    }
}
