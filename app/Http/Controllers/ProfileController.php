<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Auth;
use Validator;

class ProfileController extends Controller
{
  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit()
  {
      $id = Auth::id();
      $user = User::where('users.id', $id)->first();

      return view('admin-lte.profile.form', compact('user'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request)
  {
    $id = Auth::id();
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required',
      'msisdn' => 'required',
      'password' => 'confirmed'
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withInput()->withErrors($validator);
    }
    DB::beginTransaction();
    try {
      $user = User::find($id);
      $user->name = $request->name;
      $user->email = $request->email;
      $user->msisdn = $request->msisdn;
      $user->status = empty($request->status) ? config('constants.userStatus.inactive') : config('constants.userStatus.active');
      $user->updated_at = date('Y-m-d H:i:s');

      if (!empty($request->password)) {
        $user->password = bcrypt($request->password);
      }

      if ($request->hasFile('photo') && $request->file('photo')->isValid()){
          $file = $request->file('photo');
          $fileName = 'photo-'. $user->id . '.' . $file->getClientOriginalExtension();

          $thumbnailPath = public_path('uploads/photo/200_200_' . $fileName);
          Image::make($file->getRealPath())->fit(200, 200)->save($thumbnailPath);

          $originalPath = public_path('uploads/photo/' . $fileName);
          Image::make($file->getRealPath())->save($originalPath);

          $user->photo = $fileName;
      }

      $user->save();

      if ($user->type === config('constants.userType.member')) {
          $birthDate = null;
          if(!empty($request->birth_date)) {
            $birthDate = Carbon::createFromFormat('m/d/Y', $request->birth_date)->format('Y-m-d');
          }

          $member = Member::where('id_users', $user->id)->first();
          $member->birth_place = $request->birth_place;
          $member->birth_date = $birthDate;
          $member->gender = $request->gender;
          $member->marital_status = $request->marital_status;
          $member->education_level = $request->education_level;
          $member->education_major = $request->education_major;
          $member->education_place = $request->education_place;
          $member->education_graduated = $request->education_graduated;
          $member->job_type = $request->job_type;
          $member->job_instance = $request->job_instance;
          $member->job_skill = $request->job_skill;
          $member->job_income = $request->job_income;
          $member->job_outcome = $request->job_outcome;
          $member->health_history = $request->health_history;
          $member->health_blood_type = $request->health_blood_type;
          $member->health_bpjs = $request->health_bpjs;
          $member->health_insurance = $request->health_insurance;
          $member->health_sport = $request->health_sport;
          $member->idcard_street = $request->idcard_street;
          $member->idcard_rt = $request->idcard_rt;
          $member->idcard_rw = $request->idcard_rw;
          $member->idcard_village = $request->idcard_village;
          $member->idcard_district = $request->idcard_district;
          $member->idcard_city = $request->idcard_city;
          $member->domicile_street = $request->domicile_street;
          $member->domicile_rt = $request->domicile_rt;
          $member->domicile_rw = $request->domicile_rw;
          $member->domicile_village = $request->domicile_district;
          $member->domicile_district = $request->domicile_district;
          $member->domicile_city = $request->domicile_city;
          $member->origin_village = $request->origin_village;
          $member->origin_district = $request->origin_city;
          $member->origin_city = $request->origin_city;
          $member->origin_province = $request->origin_province;
          $member->member_type = $request->member_village;
          $member->member_village = $request->member_village;
          $member->member_district = $request->member_district;
          $member->member_area = $request->member_area;
          $member->cloth_size = $request->cloth_size;
          $member->idcard = $request->idcard;
          $member->id_jammas = $request->id_jammas;
          $member->id_cooperative = $request->id_cooperative;
          $member->id_tabarru = $request->id_tabarru;
          $member->mandiri_account = $request->mandiri_account;

          if($request->hasFile('idcard_photo') && $request->file('idcard_photo')->isValid()){
            $file = $request->file('idcard_photo');
            $fileName = 'idcard_photo-'. $user->id . '.' . $file->getClientOriginalExtension();

            $thumbnailPath = public_path('uploads/idcard_photo/200_200_' . $fileName);
            Image::make($file->getRealPath())->fit(200, 200)->save($thumbnailPath);
            $originalPath = public_path('uploads/idcard_photo/' . $fileName);
            Image::make($file->getRealPath())->save($originalPath);

            $member->idcard_photo = $fileName;
          }

          if($request->hasFile('family_card_photo') && $request->file('family_card_photo')->isValid()){
            $file = $request->file('family_card_photo');
            $fileName = 'family_card_photo-'. $user->id . '.' . $file->getClientOriginalExtension();

            $thumbnailPath = public_path('uploads/family_card_photo/200_200_' . $fileName);
            Image::make($file->getRealPath())->fit(200, 200)->save($thumbnailPath);
            $originalPath = public_path('uploads/family_card_photo/' . $fileName);
            Image::make($file->getRealPath())->save($originalPath);

            $member->family_card_photo = $fileName;
          }

          $member->save();
      }

      DB::commit();
      return redirect()->back()->with('status', 'Data updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()->withErrors([$e->getMessage()]);
    }
  }
}
