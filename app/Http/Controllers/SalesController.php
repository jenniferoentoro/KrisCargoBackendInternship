<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    public function index()
    {
        try {
            $sales = Sales::orderBy('KODE', 'asc')->get();

            return ApiResponse::json(true, 'Data retrieved successfully',  $sales);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }

    public function findByKode($KODE)
    {
        try {
            $sales = Sales::where('KODE', $KODE)->first();
            //convert to array
            $sales = $sales->toArray();
            return ApiResponse::json(true, 'Sales retrieved successfully',  $sales);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve sales', null, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'NAMA' => 'required|unique:countries,NAMA',
            'ALAMAT' => 'required',
            'HP' => 'required',
            'WA' => 'required',
            'EMAIL' => 'required',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $sales = Sales::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->first();
        if ($sales) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }



        try {
            $validatedData = $validator->validated();

            $new_sales = new Sales();
            $new_sales->KODE = Sales::withTrashed()->max('KODE') + 1;
            $new_sales->NAMA = $validatedData['NAMA'];
            $new_sales->ALAMAT = $validatedData['ALAMAT'];
            $new_sales->HP = $validatedData['HP'];
            $new_sales->WA = $validatedData['WA'];
            $new_sales->EMAIL = $validatedData['EMAIL'];
            $new_sales->save();



            if (!$new_sales) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $resp_sales = array(
                'KODE' => $new_sales->KODE,
                'NAMA' => $new_sales->NAMA,
                'ALAMAT' => $new_sales->ALAMAT,
                'HP' => $new_sales->HP,
                'WA' => $new_sales->WA,
                'EMAIL' => $new_sales->EMAIL,

            );


            return ApiResponse::json(true, "Data inserted successfully with KODE $new_sales->KODE", $resp_sales, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        $validator = Validator::make($request->all(), [
            // unique case insensitive
            'NAMA' => 'required|unique:countries,NAMA,' . $KODE . ',KODE',
            'ALAMAT' => 'required',
            'HP' => 'required',
            'WA' => 'required',
            'EMAIL' => 'required',
        ], [
            'NAMA.required' => 'The NAMA field is required.',
            'NAMA.unique' => 'The NAMA has already been taken.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive
        $sales = Sales::where(DB::raw('LOWER("NAMA")'), strtolower($request->NAMA))->where('KODE', '!=', $KODE)->first();
        if ($sales) {
            return ApiResponse::json(false, ['NAMA' => ['The NAMA has already been taken.']], null, 422);
        }

        try {
            $validatedData = $validator->validated();

            $sales = Sales::where('KODE', $KODE)->first();
            if (!$sales) {
                return ApiResponse::json(false, "Sales with KODE $KODE not found", null, 404);
            }

            $sales->NAMA = $validatedData['NAMA'];
            $sales->ALAMAT = $validatedData['ALAMAT'];
            $sales->HP = $validatedData['HP'];
            $sales->WA = $validatedData['WA'];
            $sales->EMAIL = $validatedData['EMAIL'];
            $sales->save();

            if (!$sales) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }
            $resp_sales = array(
                'KODE' => $sales->KODE,
                'NAMA' => $sales->NAMA,
                'ALAMAT' => $sales->ALAMAT,
                'HP' => $sales->HP,
                'WA' => $sales->WA,
                'EMAIL' => $sales->EMAIL,

            );

            return ApiResponse::json(true, "Data updated successfully with KODE $sales->KODE", $resp_sales, 200);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e->getMessage(), null, 500);
        }
    }
}
