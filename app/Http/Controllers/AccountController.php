<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Account;
use App\Models\CostGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function findByKode($KODE)
    {
        try {
            $User = Account::where('KODE', $KODE)->first();
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

    public function dataTableJson(Request $request)
    {
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];
            $query = Account::query();

            $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
            // Apply search filter if applicable
            if ($hasSearch) {
                $searchValue = strtoupper($request->input('search')['value']);
                $attributes = Account::first()->getAttributes(); // Get all attribute names
                $query->where(function ($q) use ($searchValue, $attributes) {
                    foreach ($attributes as $attribute => $value) {
                        $q->orWhere($attribute, 'LIKE', "%$searchValue%");
                    }
                });

                $relatedColumns = [

                    'parent' => [
                        'table' => 'accounts',
                        'column' => 'NAMA_ACCOUNT',
                    ],

                    'cost_group' => [
                        'table' => 'cost_groups',
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

            if (!empty($columnToSort)) {
                foreach ($allData as $account) {
                    $induk = Account::where('KODE', $account->INDUK)->first();
                    $account->NAMA_INDUK = $induk ? $induk->NAMA_ACCOUNT : null;

                    $account->DETIL = $account->DETIL == 1 ? 'TRUE' : 'FALSE';

                    $costGroup = CostGroup::where('KODE', $account->KODE_COST_GROUP)->first();
                    $account->NAMA_COST_GROUP = $costGroup ? $costGroup->NAMA : null;
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

            $limit = $request->get('length', 10);

            // Apply pagination and limit
            $page = $request->get('start', 0) / $limit + 1;
            $skip = ($page - 1) * $limit;
            $total_data_after_search = $query->count();
            $accounts = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($accounts as $account) {
                    $induk = Account::where('KODE', $account->INDUK)->first();
                    $account->NAMA_INDUK = $induk ? $induk->NAMA_ACCOUNT : null;

                    $account->DETIL = $account->DETIL == 1 ? 'TRUE' : 'FALSE';

                    $costGroup = CostGroup::where('KODE', $account->KODE_COST_GROUP)->first();
                    $account->NAMA_COST_GROUP = $costGroup ? $costGroup->NAMA : null;
                }
            }
            // ... rest of your code to enrich the data ...


            // Prepare the response data structure expected by the frontend

            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => Account::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $accounts->values()->toArray(),
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
            3 => ['parent', 'NAMA_ACCOUNT'],
            5 => ['cost_group', 'NAMA'],
        ];
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];
        $query = Account::query();

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
            foreach ($allData as $account) {
                $induk = Account::where('KODE', $account->INDUK)->first();
                $account->NAMA_INDUK = $induk ? $induk->NAMA_ACCOUNT : null;

                $account->DETIL = $account->DETIL == 1 ? 'TRUE' : 'FALSE';

                $costGroup = CostGroup::where('KODE', $account->KODE_COST_GROUP)->first();
                $account->NAMA_COST_GROUP = $costGroup ? $costGroup->NAMA : null;
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
        $page = $request->get('start', 0) / $limit + 1;
        $skip = ($page - 1) * $limit;
        $total_data_after_search = $query->count();
        $accounts = $allData->slice($skip)->take($limit);
        if (empty($columnToSort)) {
            foreach ($accounts as $account) {
                $induk = Account::where('KODE', $account->INDUK)->first();
                $account->NAMA_INDUK = $induk ? $induk->NAMA_ACCOUNT : null;

                $account->DETIL = $account->DETIL == 1 ? 'TRUE' : 'FALSE';

                $costGroup = CostGroup::where('KODE', $account->KODE_COST_GROUP)->first();
                $account->NAMA_COST_GROUP = $costGroup ? $costGroup->NAMA : null;
            }
        }


        // ... rest of your code to enrich the data ...




        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => Account::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $accounts->values()->toArray(),
        ];

        return ApiResponse::json(true, 'Data retrieved successfully', $response);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function indexWeb()
    {
        try {

            $accounts = Account::orderBy('KODE', 'asc')->get();
            foreach ($accounts as $account) {
                $induk = Account::where('KODE', $account->INDUK)->first();
                $account->NAMA_INDUK = $induk ? $induk->NAMA_ACCOUNT : null;

                $account->DETIL = $account->DETIL == 1 ? 'TRUE' : 'FALSE';

                $costGroup = CostGroup::where('KODE', $account->KODE_COST_GROUP)->first();
                $account->NAMA_COST_GROUP = $costGroup ? $costGroup->NAMA : null;
            }

            return ApiResponse::json(true, 'Data retrieved successfully', $accounts);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', $e, 500);
        }
    }


    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE' => 'required|numeric|unique:accounts,KODE',
            'NAMA_ACCOUNT' => 'required',
            'INDUK' => '',
            'DETIL' => 'required|numeric',
            'KODE_COST_GROUP' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'NAMA_ACCOUNT.required' => 'The NAMA ACCOUNT field is required.',
            // 'NAMA_ACCOUNT.numeric' => 'The NAMA ACCOUNT must be numeric.',

            'INDUK.numeric' => 'The INDUK must be numeric.',
            'DETIL.required' => 'The DETIL field is required.',
            'DETIL.numeric' => 'The DETIL must be numeric.',
            'KODE_COST_GROUP.required' => 'The KODE COST GROUP field is required.',
            'KODE_COST_GROUP.numeric' => 'The KODE COST GROUP must be numeric.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        // try {
        $validatedData = $validator->validated();


        $new_truck_price = new Account();
        $new_truck_price->KODE = $validatedData['KODE'];
        $new_truck_price->NAMA_ACCOUNT = $validatedData['NAMA_ACCOUNT'];
        $new_truck_price->INDUK = $validatedData['INDUK'];
        $new_truck_price->DETIL = $validatedData['DETIL'];
        $new_truck_price->KODE_COST_GROUP = $validatedData['KODE_COST_GROUP'];
        $new_truck_price->KETERANGAN = $validatedData['KETERANGAN'];
        $new_truck_price->save();
        if (!$new_truck_price) {
            return ApiResponse::json(false, 'Failed to insert data', null, 500);
        }
        $id = $new_truck_price->KODE;

        $accountsr = Account::where('KODE', $id)->first();
        // return ApiResponse::json(false, 'qw', $accountsr, 500);

        $resp_accountr = array(
            'KODE' => $accountsr->KODE,
            'NAMA_ACCOUNT' => $accountsr->NAMA_ACCOUNT,

            'INDUK' => $accountsr->INDUK,

            'NAMA_INDUK' => $accountsr->INDUK ? Account::where('KODE', $accountsr->INDUK)->first()->NAMA_ACCOUNT : "",
            'DETIL' => $accountsr->DETIL == 1 ? "TRUE" : "FALSE",
            'NAMA_COST_GROUP' => CostGroup::where('KODE', $accountsr->KODE_COST_GROUP)->first()->NAMA,
            'KETERANGAN' => $accountsr->KETERANGAN,
        );


        return ApiResponse::json(true, "Data inserted successfully with KODE $accountsr->KODE", $resp_accountr, 201);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            //kode unique except current id
            'KODE' => 'required|numeric|unique:accounts,KODE,' . $KODE . ',KODE',

            'NAMA_ACCOUNT' => 'required',
            'INDUK' => '',
            'DETIL' => 'required|numeric',
            'KODE_COST_GROUP' => 'required',
            'KETERANGAN' => 'required',
        ], [
            'NAMA_ACCOUNT.required' => 'The NAMA ACCOUNT field is required.',
            // 'NAMA_ACCOUNT.numeric' => 'The NAMA ACCOUNT must be numeric.',

            'INDUK.numeric' => 'The INDUK must be numeric.',
            'DETIL.required' => 'The DETIL field is required.',
            'DETIL.numeric' => 'The DETIL must be numeric.',
            'KODE_COST_GROUP.required' => 'The KODE COST GROUP field is required.',
            'KODE_COST_GROUP.numeric' => 'The KODE COST GROUP must be numeric.',
            'KETERANGAN.required' => 'The KETERANGAN field is required.',
        ]);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $accounts = Account::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($accounts) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        // try {
        $validatedData = $validator->validated();



        $accounts = Account::findOrFail($KODE);
        $accounts->KODE = $validatedData['KODE'];
        $accounts->NAMA_ACCOUNT = $request->get('NAMA_ACCOUNT');
        $accounts->INDUK = $request->get('INDUK');
        $accounts->DETIL = $request->get('DETIL');
        $accounts->KODE_COST_GROUP = $request->get('KODE_COST_GROUP');
        $accounts->KETERANGAN = $request->get('KETERANGAN');
        $accounts->save();
        if (!$accounts) {
            return ApiResponse::json(false, 'Failed to update data', null, 500);
        }

        $accounts = Account::where('KODE', $accounts->KODE)->first();

        $resp_account = array(
            'KODE' => $accounts->KODE,
            'NAMA_ACCOUNT' => $accounts->NAMA_ACCOUNT,

            'INDUK' => $accounts->INDUK,

            'NAMA_INDUK' => $accounts->INDUK ? Account::where('KODE', $accounts->INDUK)->first()->NAMA_ACCOUNT : "",
            'DETIL' => $accounts->DETIL == 1 ? "TRUE" : "FALSE",
            'NAMA_COST_GROUP' => CostGroup::where('KODE', $accounts->KODE_COST_GROUP)->first()->NAMA,
            'KETERANGAN' => $accounts->KETERANGAN,
        );
        return ApiResponse::json(true, 'account successfully updated', $resp_account);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, $e, null, 500);
        // }
    }

    public function destroy($KODE)
    {
        try {
            $city = Account::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'account successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete account', null, 500);
        }
    }

    public function trash()
    {
        $provinces = Account::onlyTrashed()->get();

        $resp_provinces = [];

        foreach ($provinces as $accounts) {

            $resp_province = [
                'KODE' => $accounts->KODE,
                'NAMA_ACCOUNT' => $accounts->NAMA_ACCOUNT,
                'NAMA_INDUK' => $accounts->INDUK ? Account::where('KODE', $accounts->INDUK)->first()->NAMA_ACCOUNT : "",
                'DETIL' => $accounts->DETIL,
                'NAMA_COST_GROUP' => CostGroup::where('KODE', $accounts->KODE_COST_GROUP)->first()->NAMA,
                'KETERANGAN' => $accounts->KETERANGAN,
            ];

            $resp_provinces[] = $resp_province;
        }

        return ApiResponse::json(true, 'Trash bin fetched', $resp_provinces);
    }

    public function restore($id)
    {
        $restored = Account::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        //get max KODE
        $maxKODE = Account::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
