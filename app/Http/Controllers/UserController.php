<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use DB;
use Validator;
use Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin-lte.user.index');
    }

    /**
     * Get data users.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        DB::statement(DB::raw('set @rownum=0'));
        $users = DB::table('users')
                ->select([
                  DB::raw('@rownum := @rownum + 1 AS rownum'),
                  'id',
                  'name',
                  'email'
                ]);

        $datatables = Datatables::of($users)
          ->addColumn('action', function ($users) {
              $act = ' <a href="'. route("user.edit", ['user' => $users->id]) .'"><i class="fa fa-edit"></i></a>';
              $act .= ' <a><i data-id="'. $users->id .'" class="delete fa fa-trash"></i></a>';

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin-lte.user.form', compact('roles'));
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
          'name' => 'required',
          'email' => 'required|unique:users',
          'type' => 'required',
          'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
          $user = new User;
          $user->name = $request->name;
          $user->email = $request->email;
          $user->type = $request->type;
          $user->password = empty($request->password) ? bcrypt('admin') : bcrypt($request->password);
          $user->updated_at = date('Y-m-d H:i:s');
          $user->save();

          return redirect()->route('user.index')->with('status', 'Data saved successfully!');
        } catch (\Exception $e) {
          return redirect()->back()->withInput()->withErrors([$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = User::where('id', $id)->firstOrFail();
        return view('admin-lte.user.detail', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = User::where('id', $id)->firstOrFail();
        return view('admin-lte.user.form', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'name' => 'required',
        'type' => 'required',
        'email' => 'required|unique:users,email,' . $id,
        'password' => 'confirmed',
      ]);

      if ($validator->fails()) {
          return redirect()->back()->withInput()->withErrors($validator);
      }

      try {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->type = $request->type;
        $user->updated_at = date('Y-m-d H:i:s');

        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('user.index')->with('status', 'Data updated successfully!');
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
          $user = User::where('id', $id)->firstOrFail();
          $user->delete();

          return response()->json([]);
        } catch (\Exception $e) {
          return response()->json([], $e->getStatusCode());
        }
    }
}
