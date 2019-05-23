<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HIncomecal;
use App\Http\Controllers\Controller;
use DB;
use Auth;

class DashboardController extends Controller
{
    // Dashboard
    public function index()
    {
        $fromMonth = date('m', strtotime('-3 month'));
        $toMonth = date('m');
        $fromYear = date('Y', strtotime('-3 month'));
        $toYear = date('Y');
        $idApplicator = [self::$gojek, self::$grab]; // GOJEK & GRAB
        $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
        $fromDate = "$fromYear-$fromMonth-01";
        $incomecals = [];

        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $selectQueries = DB::raw("
                    driver_name,
                    driver_id_card,
                    id_user,
                    SUM(work_days) as workdays,
                    SUM(amount * trans_value) as total_amount,
                    created_at
                ");
            } else {
                $selectQueries = DB::raw("
                    driver_name,
                    driver_id_card,
                    id_user,
                    SUM(work_days) as workdays,
                    (SUM(amount) + SUM(other_income) + SUM(incentive)) as total_amount,
                    created_at
                ");
            }

            $incomecals[$v] = HIncomecal::select($selectQueries)
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card', 'id_user', 'created_at')
                    ->get();
        }

        // Count Work Weeks
        $workweeks = [];
        foreach ($incomecals as $v => $incomecal) {
            foreach ($incomecal as $k => $value) {
                $submitDate = HIncomecal::select('created_at')
                                ->where('id_applicator', $v)
                                ->where('driver_name', $value->driver_name)
                                ->where('driver_id_card', $value->driver_id_card)
                                ->where('is_delete', 0)
                                ->orderBy('created_at', 'DESC')
                                ->first();

                $incomecal[$k]->submit_date = $submitDate->created_at;
                if ($v == self::$gojek) {
                    $workWeekData = HIncomecal::select('driver_name', 'driver_id_card', 'date_of_transaction')
                        ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                        ->where('id_applicator', $v)
                        ->where('driver_name', $value->driver_name)
                        ->where('driver_id_card', $value->driver_id_card)
                        ->where('is_delete', 0)
                        ->groupBy('driver_name', 'driver_id_card')
                        ->groupBy('date_of_transaction')
                        ->get();

                    $incomecal[$k]->workdays = count($workWeekData);
                    $workweeks[$value->driver_name][$value->driver_id_card] = (int) ceil(count($workWeekData) / 7);
                } else {
                    $workWeekData = HIncomecal::select(DB::raw("
                        SUM(work_days) as workdays
                    "))
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                        ->where('id_applicator', $v)
                        ->where('is_delete', 0)
                        ->where('driver_name', $value->driver_name)
                        ->where('driver_id_card', $value->driver_id_card)
                        ->groupBy('driver_name', 'driver_id_card', 'id_applicator')
                        ->first();

                    $workweeks[$value->driver_name][$value->driver_id_card] = (int) ceil($workWeekData->workdays / 7);
                }
            }
        }

        return view('admin-lte.dashboard.index', compact('incomecals', 'workweeks'));
    }
}
