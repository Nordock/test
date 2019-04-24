<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\HIncomecal;
use DB;
use Auth;

class DriverController extends Controller
{
    /**
     * Search driver's name
     */
    public function search(Request $request)
    {
        try {
            if ($request->has('term')) {
                $data = HIncomecal::select(
                    'driver_name',
                    'driver_id_card',
                    DB::raw('(CASE WHEN id_applicator = 1 THEN "GO-JEK" ELSE "GRAB" END) AS id_applicator')
                )->where('driver_name', 'LIKE', '%'. $request->get('term') .'%')
                ->where('is_delete', 0)
                ->groupBy('driver_name', 'driver_id_card', 'id_applicator')
                ->limit(10)
                ->get();

                return response()->json($data);
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Search driver's name
     */
    public function searchName(Request $request)
    {
        try {
            if ($request->has('term')) {
                $data = HIncomecal::select(
                    'driver_name',
                    'driver_id_card'
                )->where('driver_name', 'LIKE', '%'. $request->get('term') .'%')
                ->where('is_delete', 0)
                ->groupBy('driver_name', 'driver_id_card')
                ->limit(10)
                ->get();

                return response()->json($data);
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
