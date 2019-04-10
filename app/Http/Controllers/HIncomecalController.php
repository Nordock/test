<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use App\Models\HIncomecal;
use Carbon\Carbon;
use DB;
use Validator;
use Auth;

class HIncomecalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin-lte.hincomecal.index');
    }

    /**
     * Get data hincomecal.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        DB::statement(DB::raw('set @rownum=0'));
        $data = DB::table('h_incomecal')
                ->select([
                  DB::raw('@rownum := @rownum + 1 AS rownum'),
                  'id',
                  'date_of_transaction',
                  'amount',
                  'trans_value',
                  'trans_cost_value',
                  'trans_type',
                  'work_days',
                  'incentive',
                  'other_income',
                  'commission',
                  'rental_cost',
                  'adjustment',
                ])->where('is_delete', 0);

        if (Auth::user()->type === config('constants.userType.salesman')) {
            $data = $data->where('id_user', Auth::user()->id);
        }

        $datatables = Datatables::of($data)
          ->addColumn('action', function ($data) {
              $act = '<a><i data-id="'. $data->id .'" class="delete fa fa-trash"></i></a>';

              return $act;
          })
          ->removeColumn('id')
          ->rawColumns(['action']);

        if ($keyword = $request->get('search')['value']) {
            $datatables->filterColumn('rownum', function($query, $keyword) {
                    $sql = '@rownum + 1 like ?';
                    $query->whereRaw($sql, ["%{$keyword}%"]);
            });
        }

        return $datatables->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor' => 'required',
            'date' => 'required',
            'workdays' => 'required',
            'amount' => 'required',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::transaction(function() use ($request) {

                $dataCount = count($request->date);
                for ($i = 0; $i < $dataCount; $i += 1) {
                    $data = new HIncomecal;
                    $data->id_applicator = ($request->vendor === 'GOJEK') ? 1 : 2;
                    $data->id_user = Auth::user()->id;
                    $data->date_of_transaction = $request->date[$i];
                    $data->work_days = $request->workdays[$i];
                    $data->amount = $request->amount[$i];
                    $data->trans_type = $request->trx_type[$i];
                    $data->trans_value = $request->trx_value[$i];
                    $data->trans_cost_value = $request->trx_cost_value[$i];
                    $data->incentive = $request->incentive[$i];
                    $data->other_income = $request->other_income[$i];
                    $data->commission = $request->commission[$i];
                    $data->rental_cost = $request->rental_cost[$i];
                    $data->adjustment = $request->adjustment[$i];
                    $data->updated_at = date('Y-m-d H:i:s');
                    $data->save();
                }
            });

            return redirect()->route('upload.index')->with('status', 'Data saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors([$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = HIncomecal::where('id', $id)->firstOrFail();
            $data->is_delete = 1;
            $data->updated_at = date('Y-m-d H:i:s');
            $data->save();

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([], $e->getStatusCode());
        }
    }
}
