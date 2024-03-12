<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\PraJoa;
use App\Models\PraJoaOtherCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PraJoaOtherCostController extends Controller
{
    public function findByKode($KODE)
    {
        try {
            $othercost = PraJoaOtherCost::where('KODE', $KODE)->first();
            if (!$othercost) {
                return ApiResponse::json(false, 'Data not found', null, 404);
            }
            //convert to array
            $othercost = $othercost->toArray();
            return ApiResponse::json(true, 'Data retrieved successfully',  $othercost);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        }
    }



    public function indexWeb()
    {
        // try {
            $othercosts = PraJoaOtherCost::orderBy('KODE', 'asc')->get();


            return ApiResponse::json(true, 'Data retrieved successfully',  $othercosts);
        // } catch (\Exception $e) {
        //     return ApiResponse::json(false, 'Failed to retrieve data', null, 500);
        // }
    }

    public function storeWeb(Request $request)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_HPP_BIAYA' => 'required',
            'NOMOR_PRAJOA' => 'required',

        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }


        try {
            $validatedData = $validator->validated();


            $othercost = new PraJoaOtherCost();
            $othercost->KODE_HPP_BIAYA = $validatedData['KODE_HPP_BIAYA'];
            $othercost->NOMOR_PRAJOA = $validatedData['NOMOR_PRAJOA'];

            $othercost->save();
            if (!$othercost) {
                return ApiResponse::json(false, 'Failed to insert data', null, 500);
            }
            $id = $othercost->KODE;

            $othercostr = PraJoaOtherCost::where('KODE', $id)->first();

            //convert to array
            $othercostr = $othercostr->toArray();


            return ApiResponse::json(true, "Data inserted successfully with KODE $othercostr->KODE", $othercostr, 201);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function update(Request $request, $KODE)
    {
        //use validator here
        $validator = Validator::make($request->all(), [
            'KODE_HPP_BIAYA' => 'required',
            'NOMOR_PRAJOA' => 'required',
        ], []);

        //if validator fails
        if ($validator->fails()) {
            $errors = $validator->errors();
            return ApiResponse::json(false, $errors, null, 422);
        }

        // Check uniqueness for NAMA case insensitive except for current KODE
        // $othercosts = PraJoaOtherCost::where(DB::raw('LOWER("NAMA_BIAYA")'), strtolower($request->NAMA_BIAYA))->where('KODE', '!=', $KODE)->first();
        // if ($othercosts) {
        //     return ApiResponse::json(false, ['NAMA_BIAYA' => ['The NAMA_BIAYA has already been taken.']], null, 422);
        // }
        try {
            $validatedData = $validator->validated();



            $othercost = PraJoaOtherCost::findOrFail($KODE);
            $othercost->KODE_HPP_BIAYA = $validatedData['KODE_HPP_BIAYA'];
            $othercost->NOMOR_PRAJOA = $validatedData['NOMOR_PRAJOA'];
            $othercost->save();
            if (!$othercost) {
                return ApiResponse::json(false, 'Failed to update data', null, 500);
            }

            $othercostr = PraJoaOtherCost::where('KODE', $KODE)->first();

            //convert to array
            $othercostr = $othercostr->toArray();
            return ApiResponse::json(true, 'Other Cost successfully updated', $othercostr);
        } catch (\Exception $e) {
            return ApiResponse::json(false, $e, null, 500);
        }
    }

    public function destroy($KODE)
    {
        try {
            $city = PraJoaOtherCost::findOrFail($KODE);
            $city->delete();
            return ApiResponse::json(true, 'Other Cost successfully deleted', ['KODE' => $KODE]);
        } catch (\Exception $e) {
            return ApiResponse::json(false, 'Failed to delete othercost', null, 500);
        }
    }

    public function trash()
    {
        $othercosts = PraJoaOtherCost::onlyTrashed()->get();

        //convert to array
        $othercosts = $othercosts->toArray();


        return ApiResponse::json(true, 'Trash bin fetched', $othercosts);
    }

    public function restore($id)
    {
        $restored = PraJoaOtherCost::onlyTrashed()->findOrFail($id);
        $restored->restore();

        return ApiResponse::json(true, 'Restored', ['KODE' => $id]);
    }
}