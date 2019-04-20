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
                  DB::raw('(CASE WHEN id_applicator = 1 THEN "GO-JEK" ELSE "GRAB" END) AS id_applicator'),
                  'id',
                  'id_user',
                  'date_of_transaction',
                  'time_of_transaction',
                  'amount',
                  'trans_value',
                  'trans_cost_value',
                  'trans_type',
                  'work_days',
                  'driver_name',
                  'incentive',
                  'other_income',
                  'commission',
                  'rental_cost',
                  'adjustment',
                ])->where('is_delete', 0);

        $datatables = Datatables::of($data)
            ->editColumn('date_of_transaction', function ($data) {
                if (!empty($data->time_of_transaction)) {
                    return $data->date_of_transaction .' '. $data->time_of_transaction;
                }

                return $data->date_of_transaction;
            })
            ->addColumn('action', function ($data) {
                if (Auth::user()->id !== $data->id_user) {
                    $act = '<a><i data-id="'. $data->id .'" style="color: #CCC" class="fa fa-trash"></i></a>';
                } else {
                    $act = '<a><i data-id="'. $data->id .'" class="delete fa fa-trash"></i></a>';
                }
                

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
            'driver_name' => 'required',
            'driver_id_card' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Find if driver already owned by other salesman
        $checkDriver = HIncomecal::where('id_applicator', ($request->vendor === 'GOJEK') ? 1 : 2)
                            ->where('driver_name', $request->driver_name)
                            ->where('driver_id_card', $request->driver_id_card)
                            ->where('id_user', '!=', Auth::user()->id)
                            ->where('is_delete', 0)
                            ->first();

        if (!empty($checkDriver)) {
            return redirect()->back()->withInput()->withErrors(['This driver already managed by the other salesman : ' . $checkDriver->user->name]);
        }
        
        // For gojek handling max input var (3000)
        if ($request->vendor === 'GOJEK' && (
            count($request->date) +
            count($request->workdays) +
            count($request->amount) +
            count($request->trx_type) +
            count($request->trx_value) +
            count($request->trx_cost_value) +
            4
        ) === 10000) {
            return redirect()->back()->withInput()->withErrors(['Max input data limit exceed. Try again with more less file.']);
        }

        DB::beginTransaction();
        try {
            $dataCount = count($request->date);
            for ($i = 0; $i < $dataCount; $i += 1) {
                $data = new HIncomecal;
                $data->id_applicator = ($request->vendor === 'GOJEK') ? 1 : 2;
                $data->id_user = Auth::user()->id;
                $data->driver_name = $request->driver_name;
                $data->driver_id_card = $request->driver_id_card;
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
                $data->submit_date = date('Y-m-d');

                // Save time of transaction
                $splitDate = explode(' ', $request->date[$i]);
                if (isset($splitDate[1])) {
                    $data->time_of_transaction = $splitDate[1];
                }

                $data->save();
            }

            DB::commit();
            return redirect()->route('upload.index')->with('status', 'Data saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
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
