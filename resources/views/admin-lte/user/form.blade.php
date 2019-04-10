@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    {{ isset($data) ? 'Update User' : 'Create New User' }}
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a href="{{ route('user.index') }}">User</a></li>
    <li class="active">{{ isset($user) ? 'Update User' : 'Create New User' }}</li>
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

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form role="form" method="POST" enctype="multipart/form-data" action="{{ isset($data) ? route('user.update', ['user' => $data->id]) : route('user.store') }}">
  @if(isset($data))
    <input name="_method" type="hidden" value="PUT">
  @endif
  {{ csrf_field() }}
  <!--/.row -->

    <div class="row">
    <!-- left column -->
    <div class="col-md-12">
  <!-------------------------- DATA INFORMATION -------------------------->
  <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Data Information</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <div class="form-group">
              <label for="name">Name</label>
              <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" value="{{ $data->name or old('name') }}">
            </div>
            <div class="form-group">
              <label for="email">Email address</label>
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" value="{{ $data->email or old('email') }}">
            </div>
            <div class="form-group">
              <label for="type">Type</label>
              <select class="form-control" id="type" name="type">
                <option {!! (isset($data) && $data->type === config('constants.userType.superadmin')) ? "selected='selected'": '' !!} value="{{ config('constants.userType.superadmin') }}">Admin</option>
                <option {!! (isset($data) && $data->type === config('constants.userType.salesman')) ? "selected='selected'": '' !!} value="{{ config('constants.userType.salesman') }}">Salesman</option>
              </select>
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

      
    <!--/.col (left) -->
    <div class="box box-default">
      <div class="box-body">
          <a href="{{ route('user.index') }}" class="btn btn-default">&laquo; Back</a>
          <button type="submit" class="pull-right btn btn-primary">Submit</button>
      </div>
    </div>
  <!--/.row -->
</form> 
@endsection

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset("/bower_components/select2/dist/css/select2.min.css") }}">
@endsection

@section('scripts')
<!-- Select2 -->
<script src="{{ asset("/bower_components/select2/dist/js/select2.full.min.js") }}"></script>

<script>
$(document).ready(function() {
  //------------- CUSTOM IMAGE ---------------//
  $('#image_browser').on('click', function(e){
      e.preventDefault();
      $('#image').click();
  });

  $('#image').on('change', function(){
      $('#image_path').val($(this).val());
  });

  $('#image_path').on('click', function(){
      $('#image_browser').click();
  });
});
</script>
@endsection
