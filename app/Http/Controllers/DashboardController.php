<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HIncomecal;
use App\Http\Controllers\Controller;
use DB;
use Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
      $fromMonth = date('m', strtotime('-3 months'));;
      $toMonth = date('m');
      $fromYear = date('Y', strtotime('-3 months'));
      $toYear = date('Y');
      $applicators = [
        self::$gojek,
        self::$grab
      ];
      $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
      $fromDate = "$fromYear-$fromMonth-01";

      $incomecals = [];

      foreach ($applicators as $v) {
        if ($v == self::$gojek) {
            $selectQueries = DB::raw("
                SUM(work_days) as workdays,
                SUM(CASE WHEN trans_type LIKE '%CREDIT%' THEN amount ELSE 0 END) as total_amount,
                SUM(CASE WHEN trans_type LIKE '%DEBIT%' THEN amount ELSE 0 END) as total_expense,
                MONTH(date_of_transaction) AS month,
                YEAR(date_of_transaction) AS year
            ");
        } else {
            $selectQueries = DB::raw("
                SUM(work_days) as workdays,
                (SUM(amount) + SUM(other_income) + SUM(incentive)) as total_amount,
                (SUM(commission) + SUM(rental_cost) + SUM(adjustment)) as total_expense,
                MONTH(date_of_transaction) AS month,
                YEAR(date_of_transaction) AS year
            ");
        }

        $incomecals[$v] = HIncomecal::select($selectQueries)
                        ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                        ->where('id_applicator', $v);

        if (Auth::user()->type == config('constants.userType.salesman')) {
            $incomecals[$v] = $incomecals[$v]->where('id_user', Auth::user()->id);
        }

        $incomecals[$v] = $incomecals[$v]->groupBy(DB::raw('MONTH(date_of_transaction)'))
                        ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                        ->get();
      }

      return view('admin-lte.dashboard.index', compact('incomecals'));
    }
}
