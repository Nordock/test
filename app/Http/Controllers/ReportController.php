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
        $from_month_options = $this->generateMonthOptions();
        $from_year_options = $this->generateYearOptions();
        $to_month_options = $this->generateMonthOptions();
        $to_year_options = $this->generateYearOptions();
        $user_options = $this->generateUserOptions();
        $incomecal = [];

        return view('admin-lte.report.index', compact(
            'incomecal',
            'to_month_options',
            'to_year_options',
            'from_year_options',
            'from_month_options',
            'user_options'
        ));
    }

    /**
     * Generate year select options
     */
    public function generateYearOptions($selected = '')
    {
        $maxYearReport = 5;
        $options = '';
        foreach (range(date('Y'), date('Y') - $maxYearReport) as $x) {
            $options .= '<option value="'.$x.'"'.($x == $selected ? ' selected="selected"' : '').'>' . $x . '</option>';
        }

        return $options;
    }

    /**
     * Generate month select options
     */
    public function generateMonthOptions($selected = '')
    {
        $months = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
        
        $options = '';
        foreach ($months as $x => $v) {
            $options .= '<option value="'.$x.'"'.($x == $selected ? ' selected="selected"' : '').'>' . $v . '</option>';
        }

        return $options;
    }

    /**
     * Generate user options
     */
    public function generateUserOptions($selected = '')
    {
        $users = User::where('type', config('constants.userType.salesman'));
        if (Auth::user()->type === config('constants.userType.salesman')) {
            $users = $users->where('id', Auth::user()->id);
            $selected = Auth::user()->id;
        }
        $users = $users->get();

        $options = '';
        foreach ($users as $x => $v) {
            $options .= '<option value="'.$v->id.'"'.($v->id == $selected ? ' selected="selected"' : '').'>' . $v->name . '</option>';
        }

        return $options;
    }

    /**
     * Generate report.
     */
    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_month' => 'required',
            'to_month' => 'required',
            'from_year' => 'required',
            'to_year' => 'required',
            'id_applicator' => 'required',
            'id_user' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $fromMonth = $request->get('from_month');
        $toMonth = $request->get('to_month');
        $fromYear = $request->get('from_year');
        $toYear = $request->get('to_year');
        $idApplicator = $request->get('id_applicator');
        $applicator = ($idApplicator == self::$gojek) ? 'GOJEK' : 'GRAB';
        $idUser = Auth::user()->type === config('constants.userType.salesman') ? Auth::user()->id : $request->get('id_user');
        $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
        $fromDate = "$fromYear-$fromMonth-01";

        $incomecal = [];

        if ($idApplicator == self::$gojek) {
            $selectQueries = DB::raw("
                SUM(work_days) as workdays,
                SUM(CASE WHEN trans_type NOT LIKE '%GO-PAY%' THEN amount ELSE 0 END) as total_amount,
                SUM(CASE WHEN trans_type LIKE '%GO-PAY%' THEN amount ELSE 0 END) as total_expense,
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
        
        $incomecal = HIncomecal::select($selectQueries)
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $idApplicator)
                    ->where('id_user', $idUser)
                    ->where('is_delete', 0)
                    ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                    ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                    ->get();

        $user = User::where('id', $idUser)->firstOrFail();
        $applicant = $user->name;
        $from_month_options = $this->generateMonthOptions($fromMonth);
        $from_year_options = $this->generateYearOptions($fromYear);
        $to_month_options = $this->generateMonthOptions($toMonth);
        $to_year_options = $this->generateYearOptions($toYear);
        $user_options = $this->generateUserOptions($idUser);
        $inputs = $request->except('_token');

        return view('admin-lte.report.index', compact(
            'incomecal',
            'applicator',
            'to_month_options',
            'to_year_options',
            'from_year_options',
            'from_month_options',
            'user_options',
            'applicant',
            'inputs'
        ));

    }

    /**
     * Download Report
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_month' => 'required',
            'to_month' => 'required',
            'from_year' => 'required',
            'to_year' => 'required',
            'id_applicator' => 'required',
            'id_user' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $inputs = $request->all();
        $fromMonth = $request->get('from_month');
        $toMonth = $request->get('to_month');
        $fromYear = $request->get('from_year');
        $toYear = $request->get('to_year');
        $idApplicator = $request->get('id_applicator');
        $applicator = ($idApplicator == self::$gojek) ? 'GOJEK' : 'GRAB';
        $idUser = Auth::user()->type === config('constants.userType.salesman') ? Auth::user()->id : $request->get('id_user');
        $toLastDate = date("Y-m-t", strtotime("$toYear-$toMonth-01"));
        $fromDate = "$fromYear-$fromMonth-01";

        $incomecal = [];

        if ($idApplicator == self::$gojek) {
            $selectQueries = DB::raw("
                SUM(work_days) as workdays,
                SUM(CASE WHEN trans_type NOT LIKE '%GO-PAY%' THEN amount ELSE 0 END) as total_amount,
                SUM(CASE WHEN trans_type LIKE '%GO-PAY%' THEN amount ELSE 0 END) as total_expense,
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
        
        $incomecal = HIncomecal::select($selectQueries)
                    ->whereBetween('date_of_transaction', [$fromDate, $toLastDate])
                    ->where('id_applicator', $idApplicator)
                    ->where('id_user', $idUser)
                    ->where('is_delete', 0)
                    ->groupBy(DB::raw('MONTH(date_of_transaction)'))
                    ->groupBy(DB::raw('YEAR(date_of_transaction)'))
                    ->get();
                    
        return Excel::download(new ReportExport(['incomecal' => $incomecal]), date('Y-m-d') . '-report.xlsx');
    }
}
