@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    User Data
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">User</li>
  </ol>
</section>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header row">
        <div class="col-md-12">
          <a class="pull-right btn btn-primary" href="{{ route('user.create') }}">Create New</a>
        </div>
      </div>

      <!-- /.box-header -->
      <div class="box-body table-responsive">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <table id="data-table" class="table table-bordered table-hover">
          <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Action</th>
          </tr>
          </thead>
        </table>
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->

  </div>
  <!-- /.col -->
</div>
<!-- /.row -->
@endsection

@section('styles')
<!-- Sweet Alert -->
<link rel="stylesheet" href="{{ asset("/bower_components/bootstrap-sweetalert/dist/sweetalert.css") }}">
@endsection

@section('scripts')
<!-- Sweet Alert -->
<script src="{{ asset("/bower_components/bootstrap-sweetalert/dist/sweetalert.js") }}"></script>
<script>
$(document).ready(function() {
  function init() {
      let table = $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        ajax: '{!! route('user.data') !!}',
        columns: [
          { data: 'rownum', name: 'rownum'},
          { data: 'name', name: 'name' },
          { data: "action", orderable: false, searchable: false, class: "text-left" }
        ],
        fnDrawCallback: () => {
          $(".delete").on('click', onDelete);
        },
        initComplete: () => {
          $(".delete").on('click', onDelete);
        }
      });
  }

  init();

  //---------- SWEET ALERT ------------//
  function onDelete(data) {
    data.preventDefault();
    return swal({
      title: "Are you sure?",
      text: "Your will not be able to recover this data!",
      type: "warning",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes, delete it!",
      closeOnConfirm: false
    }, () => {
      let url = '{{ route("user.destroy", [ "user" => ":id"]) }}';
      url = url.replace(':id', $(this).data('id'));
      $.ajax({
        url: url,
        type: 'json',
        data: '_token={{ csrf_token() }}',
        method: 'DELETE',
        error: () => {
          swal("Error!", "Failed to delete data user. Please try again later.", "error");
        },
        success: () => {
          swal("Deleted!", "Data user has been deleted.", "success");
          init();
        }
      });
    });
  }
});
</script>
@endsection
