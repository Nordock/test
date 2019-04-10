@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    Update Profile
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a class="active">Update Profile</a></li>
  </ol>
</section>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
<form role="form" method="POST" enctype="multipart/form-data" action="{{ route('profile.update') }}">
  <input name="_method" type="hidden" value="PUT">
  {{ csrf_field() }}
  <div class="row">
    <!-- left column -->
    <div class="col-md-12">
      <!-- general form elements -->
      <!--------------- ACCOUNT INFORMATION -------------->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Account Information</h3>
        </div>

        <div class="box-body">
          <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" value="{{ $user->email or old('email') }}">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
          </div>
          <div class="form-group">
            <label for="password_confirmation">Password Confirmation</label>
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Password Confirmation">
          </div>
        </div>
      </div>
        <!-- /.box-body -->
  <!-------------------------- PERSONAL INFORMATION -------------------------->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Personal Information</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" value="{{ $user->name or old('name') }}">
          </div>
          <div class="form-group">
            <label for="msisdn">Phone Number</label>
            <input type="text" name="msisdn" class="form-control" id="msisdn" placeholder="Enter Phone Number" value="{{ $user->msisdn or old('msisdn') }}">
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <div>
              <input type="checkbox" name="status" id="status" class="minimal" value="1" {{ ((isset($user) && $user->status) or old('status')) == 1 ? "checked" : "" }}> Active
            </div>
          </div>
          <div class="form-group">
            <label for="photo">Photo</label>
            @if(isset($user) && !empty($user->photo))
            <div class="input-group">
              <a href="{{ asset('uploads/photo/'. $user->photo) }}">
                <img class="thumbnail" src="{{ asset('uploads/photo/200_200_'. $user->photo) }}" />
              </a>
            </div>
            @endif
            <div class="input-group">
                <input required readonly type="text" id="photo_path" class="form-control" placeholder="Browse...">
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="button" id="photo_browser">
                  <i class="fa fa-search"></i> Browse</button>
                </span>
            </div>
            <input type="file" class="hidden" id="photo" name="photo">
            <p class="help-block">Format file allowed : png, jpg.</p>
          </div>
        </div>
      </div>
      <!-- /.box-body -->

      </div>
    </div>
    <!--/.col (left) -->
    <div class="box box-default">
      <div class="box-body">
          <button type="submit" class="pull-right btn btn-primary">Submit</button>
      </div>
    </div>
  <!--/.row -->
</form>
@endsection

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset("/bower_components/select2/dist/css/select2.min.css") }}">
<!-- Datepicker -->
<link rel="stylesheet" href="{{ asset("/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css") }}">
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset("/bower_components/admin-lte/plugins/iCheck/all.css") }}">
@endsection

@section('scripts')
<!-- iCheck -->
<script src="{{ asset("/bower_components/admin-lte/plugins/iCheck/icheck.min.js") }}"></script>
<!-- Select2 -->
<script src="{{ asset("/bower_components/select2/dist/js/select2.full.min.js") }}"></script>
<!-- Datepicker -->
<script src="{{ asset("/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js") }}"></script>

<script>
$(document).ready(function() {
  $('#password').val('');

  //------------- CUSTOM PHOTO ---------------//
  $('#photo_browser').on('click', function(e){
      e.preventDefault();
      $('#photo').click();
  });

  $('#photo').on('change', function(){
      $('#photo_path').val($(this).val());
  });

  $('#photo_path').on('click', function(){
      $('#photo_browser').click();
  });

  //------------- CUSTOM ID CARD PHOTO ---------------//
  $('#idcard_photo_browser').on('click', function(e){
      e.preventDefault();
      $('#idcard_photo').click();
  });

  $('#idcard_photo').on('change', function(){
      $('#idcard_photo_path').val($(this).val());
  });

  $('#idcard_photo_path').on('click', function(){
      $('#idcard_photo_browser').click();
  });

  //------------- CUSTOM FAMILY CARD PHOTO ---------------//
  $('#family_card_photo_browser').on('click', function(e){
      e.preventDefault();
      $('#family_card_photo').click();
  });

  $('#family_card_photo').on('change', function(){
      $('#family_card_photo_path').val($(this).val());
  });

  $('#family_card_photo_path').on('click', function(){
      $('#family_card_photo_browser').click();
  });

  //----------- DATEPICKER -----------//
  $('#birth_date').datepicker({
    autoclose: true
  });

  //----------- SELECT 2 ------------//
  $('#health_blood_type').select2({
    placeholder: "-- Select blood type --",
    allowClear: true
  });

  $('#member_type').select2({
    placeholder: "-- Select member type --",
    allowClear: true
  });

  $('#cloth_size').select2({
    placeholder: "-- Select cloth size --",
    allowClear: true
  });

  $('#education_level').select2({
    placeholder: "-- Select education level --",
    allowClear: true
  });

  //------------ MINIMAL ------------//
  $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue'
  })
});
</script>
@endsection
