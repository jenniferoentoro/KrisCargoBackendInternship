<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\GetUserFromToken;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use ParagonIE\Paseto\Exception\InvalidVersionException;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\IssuedBy;
use ParagonIE\Paseto\Rules\ValidAt;

class UserController extends Controller
{

    public function dataTableJson(Request $request)
    {
        // try {
        $order = $request->input('order')[0];
        $columnIndex = (int) $order['column'];
        $sortDirection = $order['dir'];

        $columns = $request->input('columns');
        $columnToSort = $columns[$columnIndex]['data'];

        $query = User::query();

        $hasSearch = $request->has('search') && !empty($request->input('search')['value']);
        $dateColumns = []; // Add your date column names here
        // Apply search filter if applicable
        if ($hasSearch) {
            $searchValue = strtoupper($request->input('search')['value']);
            $attributes = User::first()->getAttributes(); // Get all attribute names
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
                'positions' => [
                    'table' => 'positions',
                    'column' => 'NAMA',
                ]

            ];

            foreach ($relatedColumns as $relation => $relatedColumn) {
                $query->orWhereHas($relation, function ($q) use ($searchValue, $relatedColumn) {
                    $q->where($relatedColumn['column'], 'LIKE', "%$searchValue%");
                });
            }
        }

        $query->orderBy('KODE', 'asc');

        //order by kode integer ascending


        $allData = $query->get();



        if (!empty($columnToSort)) {
            foreach ($allData as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
            }
            $sortBoolean = $sortDirection === 'asc' ? false : true;
            //check if columnToSort is KODE
            if ($columnIndex === 1) {


                //include the sort boolean
                $allData = $allData->sortBy(function ($data) {
                    return (int) $data->KODE;
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
        $Users = $allData->slice($skip)->take($limit);



        // ... rest of your code to enrich the data ...
        if (empty($columnToSort)) {
            foreach ($Users as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
            }
        }



        // Prepare the response data structure expected by the frontend

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => User::count(),
            'recordsFiltered' => $total_data_after_search,
            'data' => $Users->values()->toArray(),
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
            4 => ['positions', 'NAMA'],

        ];
        try {
            $order = $request->input('order')[0];
            $columnIndex = (int) $order['column'];
            $sortDirection = $order['dir'];

            $columns = $request->input('columns');
            $columnToSort = $columns[$columnIndex]['data'];

            $query = User::query();

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

            $query->orderBy('KODE', 'asc');


            $allData = $query->get();



            if (!empty($columnToSort)) {
                foreach ($allData as $User) {
                    $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                }
                $sortBoolean = $sortDirection === 'asc' ? false : true;
                //check if columnToSort is KODE
                if ($columnIndex === 1) {


                    //include the sort boolean
                    $allData = $allData->sortBy(function ($data) {
                        return (int) $data->KODE;
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

            $Users = $allData->slice($skip)->take($limit);

            if (empty($columnToSort)) {
                foreach ($Users as $User) {
                    $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
                }
            }


            // ... rest of your code to enrich the data ...




            $response = [
                'draw' => $request->input('draw'),
                'recordsTotal' => User::count(),
                'recordsFiltered' => $total_data_after_search,
                'data' => $Users->values()->toArray(),
            ];

            return ApiResponse::json(true, 'Data retrieved successfully', $response);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }
    public function findByKode($KODE)
    {
        try {
            $User = User::where('KODE', $KODE)->first();
            if (!$User) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            $User = $User->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function index()
    {
        try {
            $Users = User::all();
            foreach ($Users as $User) {
                $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;
            }
            return ApiResponse::json(true, 'Data retrieved successfully', $Users);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required',
            'EMAIL' => 'required|email|unique:users,EMAIL',
            'PASSWORD' => 'required|min:8',
            'KODE_JABATAN' => 'required',
        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = User::where(DB::raw('LOWER("EMAIL")'), strtolower($request->EMAIL))->first();
        if ($existingData) {

            return ApiResponse::json(false, ['EMAIL' => ['The EMAIL has already been taken.']], null, 422);
        }
        try {



            $newUser = new User();
            $newUser->KODE = User::withTrashed()->max('KODE') + 1;
            $newUser->NAMA = $request->get('NAMA');
            $newUser->EMAIL = $request->get('EMAIL');
            $newUser->PASSWORD = bcrypt($request->get('PASSWORD'));

            $newUser->KODE_JABATAN = $request->get('KODE_JABATAN');

            $newUser->save();


            if ($newUser) {
                //get nama jabatan
                $nama_jabatan = Position::where('KODE', $request->get('KODE_JABATAN'))->first()->NAMA;
                // create associative array
                $resp_User = User::where('KODE', $newUser->KODE)->first()->toArray();
                $resp_User['NAMA_JABATAN'] = $nama_jabatan;

                return ApiResponse::json(true, "Data inserted successfully with KODE $newUser->KODE", $resp_User);
            }
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            'NAMA' => 'required',
            'EMAIL' => 'required|email|unique:users,EMAIL,' . $KODE . ',KODE',
            // 'PASSWORD' => 'required',
            'KODE_JABATAN' => 'required',

        ], []);



        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        $existingData = User::where(DB::raw('LOWER("EMAIL")'), strtolower($request->EMAIL))->where('KODE', '!=', $KODE)->first();
        if ($existingData) {

            return ApiResponse::json(false, ['EMAIL' => ['The EMAIL has already been taken.']], null, 422);
        }
        try {
            $User = User::findOrFail($KODE);
            $User->NAMA = $request->get('NAMA');
            $User->EMAIL = $request->get('EMAIL');
            // $User->PASSWORD = bcrypt($request->get('PASSWORD'));
            $User->KODE_JABATAN = $request->get('KODE_JABATAN');

            $User->save();

            if (!$User) {
                return ApiResponse::json(false, 'Failed to update User', null, 500);
            }



            $User->NAMA_JABATAN = Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA;

            return ApiResponse::json(true, 'User successfully updated', $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $User = User::findOrFail($KODE);
            $User->delete();
            return ApiResponse::json(true, 'User successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete User', null, 500);
        }
    }



    public function trash()
    {
        $Users = User::onlyTrashed()->get();

        foreach ($Users as $User) {
            // get nama jabatan
            $User->setAttribute('NAMA_JABATAN', Position::where('KODE', $User->KODE_JABATAN)->first()->NAMA);
        }

        return ApiResponse::json(true, 'Trash bin fetched', $Users);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'KODE' => 'required',
        ], []);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        try {
            $User = User::findOrFail($request->get('KODE'));
            $User->PASSWORD = bcrypt('password');
            $User->save();

            return ApiResponse::json(true, 'Password successfully reset', $User);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to reset password', null, 500);
        }
    }

    public function changePassword(Request $request)
    {
        //validator rules
        $validator = Validator::make($request->all(), [
            'OLD_PASSWORD' => 'required|min:8',
            'NEW_PASSWORD' => 'required|min:8',
        ], []);

        // If validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }
        //get user from token
        $providedToken = $request->bearerToken();


        if (!$providedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        try {
            $user = GetUserFromToken::getUser($providedToken);
            // check if old password is correct
            if (!Hash::check($request->get('OLD_PASSWORD'), $user->PASSWORD)) {
                return ApiResponse::json(false, ['OLD_PASSWORD' => ['Current password is incorrect!']], null, 422);
            }

            $user->PASSWORD = bcrypt($request->get('NEW_PASSWORD'));
            $user->save();

            return ApiResponse::json(true, 'Password successfully changed', $user);
        } catch (InvalidVersionException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (PasetoException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function restore($id)
    {
        $restored = User::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }

    public function getNextId()
    {
        // $tableName = 'countries'; // Replace with your table name
        // $autoIncrementColumn = 'KODE'; // Replace with your auto increment column name
        // $sequenceName = $tableName . '_' . $autoIncrementColumn . '_seq';

        // $query = DB::raw("SELECT nextval('\"$sequenceName\"') AS next_id");

        // $result = DB::select($query);

        // $nextId = $result[0]->next_id;

        // return response()->json(['next_id' => $nextId]);

        //get max KODE
        $maxKODE = User::withTrashed()->max('KODE');
        //get next KODE
        $nextKODE = $maxKODE + 1;
        return ApiResponse::json(true, 'Data retrieved successfully',  $nextKODE);
    }
}
