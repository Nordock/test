<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HIncomecal;
use App\Models\User;
use App\Exports\ReportExport;
use Auth;
use DB;
use Validator;
use Excel;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $submitOptions = $this->getSubmitHistoryOptions($request);
        return view('admin-lte.report.index', [
            'submit_options' => $submitOptions
        ]);
    }

    /**
     * Generate report.
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_name' => 'required',
            'driver_id_card' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $checkDriver = HIncomecal::where('driver_name', $request->driver_name)->where('driver_id_card', $request->driver_id_card)->first();
        if (empty($checkDriver)) {
            return redirect()->back()->withInput()->withErrors(['No driver were found']);
        }

        // Define filter date report
        $fromMonth = date('m', strtotime('-3 month'));
        $toMonth = date('m');
        $fromYear = date('Y', strtotime('-3 month'));
        $toYear = date('Y');
        $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
        $fromDate = "$fromYear-$fromMonth-01";

        // Get month by submit date
        if ($request->has('submit_date') && !empty($request->get('submit_date'))) {
            if (!empty($getReportDate)) {
                $fromMonth = date('m', strtotime('-3 month', strtotime($request->get('submit_date'))));
                $toMonth = date('m', strtotime($request->get('submit_date')));
                $fromYear = date('Y', strtotime('-3 month', strtotime($request->get('submit_date'))));
                $toYear = date('Y', strtotime($request->get('submit_date')));
                $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
                $fromDate = "$fromYear-$fromMonth-01";
            }
        }

        $idApplicator = [self::$gojek, self::$grab]; // GOJEK & GRAB
        $idUser = Auth::user()->type === config('constants.userType.salesman') ? Auth::user()->id : $request->get('id_user');
        $incomecals = [];

        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $selectQueries = DB::raw("
                    SUM(work_days) as workdays,
                    SUM(amount * trans_value) as total_amount,
                    SUM(amount * trans_cost_value) as total_expense,
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
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                    ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                    ->get();
        }

        $newIncomecals = [];
        // Set default months
        foreach ($incomecals as $k => $incomevalue) {
            $lastCountMonths = 4;
            $fromDefaultYear = $fromYear;
            $fromDefaultMonth = $fromMonth;

            $data = [];
            if (count ($incomevalue) == 0) {
                $newIncomecals[$k] = [];
                continue;
            }
            for ($i = 0; $i < $lastCountMonths; $i += 1) {
                $monthIncome = new \stdClass();
                $monthIncome->workdays = 0;
                $monthIncome->total_amount = 0;
                $monthIncome->total_expense = 0;
                $monthIncome->month = $fromDefaultMonth;
                $monthIncome->year = $fromDefaultYear;

                foreach ($incomevalue as $val) {
                    if ($val->month == $fromDefaultMonth) {
                        // For gojek need more calculation for workdays
                        if ($k == self::$gojek) {
                            $workWeekData = HIncomecal::select('driver_name', 'driver_id_card', 'date_of_transaction')
                                ->whereRaw('MONTH(date_of_transaction) = "'. $val->month .'"')
                                ->where('id_applicator', $k)
                                ->where('driver_name', $request->get('driver_name'))
                                ->where('driver_id_card', $request->get('driver_id_card'))
                                ->where('is_delete', 0)
                                ->groupBy('driver_name', 'driver_id_card')
                                ->groupBy('date_of_transaction')
                                ->get();

                            $val->workdays = count($workWeekData);
                        }

                        $monthIncome = $val;
                        break;
                    }
                }

                $data[] = $monthIncome;

                $fromDefaultMonth += 1;
                if ($fromDefaultMonth > 12) {
                    $fromDefaultMonth = 1;
                    $fromDefaultYear += 1;
                }
            }

            $newIncomecals[$k] = $data;
        }

        // Count Work Weeks
        $workweeks = [];
        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $workWeekData = HIncomecal::select('driver_name', 'driver_id_card', 'date_of_transaction')
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->groupBy('date_of_transaction')
                    ->get();

                $workweeks[$v] = (int) ceil(count($workWeekData) / 7);
            } else {
                $workWeekData = HIncomecal::select(DB::raw("
                    SUM(work_days) as workdays
                "))
                ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->first();

                $workweeks[$v] = isset($workWeekData->workdays) ? (int) ceil($workWeekData->workdays / 7) : 0;
            }
        }
        // Count average 3 month
        $av3monthIncGross = [];
        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $avData1 = HIncomecal::select(DB::raw("
                      SUM(amount * trans_value) as total_amount,
                      MONTH(date_of_transaction) AS month,
                      YEAR(date_of_transaction) AS year
                    "))
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->whereRaw('date_of_transaction >= last_day(now()) + interval 1 day - interval 3 month')
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                    ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                    ->get();

                $av3monthIncGrosss[$v] += $avData1->total_amount;
            } else {
              $avData1 = HIncomecal::select(DB::raw("
                    (SUM(amount) + SUM(other_income) + SUM(incentive)) as total_amount,
                    MONTH(date_of_transaction) AS month,
                    YEAR(date_of_transaction) AS year
                  "))
                  ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                  ->where('id_applicator', $v)
                  ->where('driver_name', $request->get('driver_name'))
                  ->where('driver_id_card', $request->get('driver_id_card'))
                  ->whereRaw('date_of_transaction >= last_day(now()) + interval 1 day - interval 3 month')
                  ->where('is_delete', 0)
                  ->groupBy('driver_name', 'driver_id_card')
                  ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                  ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                  ->get();

                $av3monthIncGrosss[$v] += $avData1->total_amount;
            }
        }

        $inputs = $request->except('_token');
        $submitOptions = $this->getSubmitHistoryOptions($request);
        return view('admin-lte.report.index', [
            'incomecals' => $newIncomecals,
            'submit_options' => $submitOptions,
            'workweeks' => $workweeks,
            'inputs' => $inputs
        ]);
    }

    /**
     * Get submit history options
     */
    public function getSubmitHistoryOptions(Request $request)
    {
        $options = '<option value="">- Last 4 Months -</option>';
        if ($request->has('submit_date') && !empty($request->get('submit_date'))) {
            $data = HIncomecal::select('submit_date')
            ->where('driver_name', $request->get('driver_name'))
            ->where('driver_id_card', $request->get('driver_id_card'))
            ->where('is_delete', 0)
            ->groupBy('submit_date', 'driver_name', 'driver_id_card')
            ->get();

            foreach ($data as $k => $v) {
                if ($v->submit_date === $request->get('submit_date')) {
                    $options .= '<option selected value="'. $v->submit_date .'">'. $v->submit_date .'</option>';
                } else {
                    $options .= '<option value="'. $v->submit_date .'">'. $v->submit_date .'</option>';
                }
            }
        }

        return $options;
    }

    /**
     * Download Report
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_name' => 'required',
            'driver_id_card' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $checkDriver = HIncomecal::where('driver_name', $request->driver_name)->where('driver_id_card', $request->driver_id_card)->first();
        if (empty($checkDriver)) {
            return redirect()->back()->withInput()->withErrors(['No driver were found']);
        }

        // Define filter date report
        $fromMonth = date('m', strtotime('-3 month'));
        $toMonth = date('m');
        $fromYear = date('Y', strtotime('-3 month'));
        $toYear = date('Y');
        $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
        $fromDate = "$fromYear-$fromMonth-01";

        // Get month by submit date
        if ($request->has('submit_date') && !empty($request->get('submit_date'))) {
            if (!empty($getReportDate)) {
                $fromMonth = date('m', strtotime('-3 month', strtotime($request->get('submit_date'))));
                $toMonth = date('m', strtotime($request->get('submit_date')));
                $fromYear = date('Y', strtotime('-3 month', strtotime($request->get('submit_date'))));
                $toYear = date('Y', strtotime($request->get('submit_date')));
                $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
                $fromDate = "$fromYear-$fromMonth-01";
            }
        }

        $idApplicator = [self::$gojek, self::$grab]; // GOJEK & GRAB
        $idUser = Auth::user()->type === config('constants.userType.salesman') ? Auth::user()->id : $request->get('id_user');
        $incomecals = [];

        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $selectQueries = DB::raw("
                    SUM(work_days) as workdays,
                    SUM(amount * trans_value) as total_amount,
                    SUM(amount * trans_cost_value) as total_expense,
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
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                    ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                    ->get();
        }

        $newIncomecals = [];
        // Set default months
        foreach ($incomecals as $k => $incomevalue) {
            $lastCountMonths = 4;
            $fromDefaultYear = $fromYear;
            $fromDefaultMonth = $fromMonth;

            $data = [];
            if (count ($incomevalue) == 0) {
                $newIncomecals[$k] = [];
                continue;
            }

            for ($i = 0; $i < $lastCountMonths; $i += 1) {
                $monthIncome = new \stdClass();
                $monthIncome->workdays = 0;
                $monthIncome->total_amount = 0;
                $monthIncome->total_expense = 0;
                $monthIncome->month = $fromDefaultMonth;
                $monthIncome->year = $fromDefaultYear;

                foreach ($incomevalue as $val) {
                    if ($val->month == $fromDefaultMonth) {
                        // For gojek need more calculation for workdays
                        if ($k == self::$gojek) {
                            $workWeekData = HIncomecal::select('driver_name', 'driver_id_card', 'date_of_transaction')
                                ->whereRaw('MONTH(date_of_transaction) = "'. $val->month .'"')
                                ->where('id_applicator', $k)
                                ->where('driver_name', $request->get('driver_name'))
                                ->where('driver_id_card', $request->get('driver_id_card'))
                                ->where('is_delete', 0)
                                ->groupBy('driver_name', 'driver_id_card')
                                ->groupBy('date_of_transaction')
                                ->get();

                            $val->workdays = count($workWeekData);
                        }

                        $monthIncome = $val;
                        break;
                    }
                }

                $data[] = $monthIncome;

                $fromDefaultMonth += 1;
                if ($fromDefaultMonth > 12) {
                    $fromDefaultMonth = 1;
                    $fromDefaultYear += 1;
                }
            }

            $newIncomecals[$k] = $data;
        }

        // Count Work Weeks
        $workweeks = [];
        foreach ($idApplicator as $v) {
            if ($v == self::$gojek) {
                $workWeekData = HIncomecal::select('driver_name', 'driver_id_card', 'date_of_transaction')
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->groupBy('date_of_transaction')
                    ->get();

                $workweeks[$v] = (int) ceil(count($workWeekData) / 7);
            } else {
                $workWeekData = HIncomecal::select(DB::raw("
                    SUM(work_days) as workdays
                "))
                ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $v)
                    ->where('driver_name', $request->get('driver_name'))
                    ->where('driver_id_card', $request->get('driver_id_card'))
                    ->where('is_delete', 0)
                    ->groupBy('driver_name', 'driver_id_card')
                    ->first();

                $workweeks[$v] = isset($workWeekData->workdays) ? (int) ceil($workWeekData->workdays / 7) : 0;
            }
        }

        return Excel::download(new ReportExport([
            'incomecals' => $newIncomecals,
            'workweeks' => $workweeks,
        ]), str_slug($request->get('driver_name')). '-'. date('Y-m-d') . '.xlsx');
    }
}
