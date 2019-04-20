@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    Error
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
  </ol>
</section>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body text-center">
        <h1><i class="fa fa-lock"></i> {{ $exception->getMessage() }}</h1>
      <!-- /.box-body -->
      </div>
    </div>
    <!-- /.box -->

  </div>
  <!-- /.col -->
</div>
<!-- /.row -->
@endsection
