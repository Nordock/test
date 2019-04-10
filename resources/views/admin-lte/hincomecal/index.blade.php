@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    Transaction Data
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Transaction</li>
  </ol>
</section>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header row">
        <div class="col-md-12">
          <a class="pull-right btn btn-primary" href="{{ route('upload.upload') }}">+ Add Data</a>
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
            <th width="13%">Date</th>
            <th width="5%">Amount</th>
            <th width="15%">Trx Type</th>
            <th width="8%">Trx Value</th>
            <th width="10%">Trx Cost</th>
            <th width="8%">Incentive</th>
            <th width="10%">Other Income</th>
            <th width="8%">Commission</th>
            <th width="8%">Rental Cost</th>
            <th width="8%">Adjustment</th>
            <th width="3%">Workdays</th>
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
        ajax: '{!! route('hincomecal.data') !!}',
        columns: [
          { data: 'date_of_transaction', name: 'date_of_transaction' },
          { data: 'amount', name: 'amount' },
          { data: 'trans_type', name: 'trans_type' },
          { data: 'trans_value', name: 'trans_value' },
          { data: 'trans_cost_value', name: 'trans_cost_value' },
          { data: 'incentive', name: 'incentive' },
          { data: 'other_income', name: 'other_income' },
          { data: 'commission', name: 'commission' },
          { data: 'rental_cost', name: 'rental_cost' },
          { data: 'adjustment', name: 'adjustment' },
          { data: 'work_days', name: 'work_days' },
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
      let url = '{{ route("hincomecal.destroy", [ "hincomecal" => ":id"]) }}';
      url = url.replace(':id', $(this).data('id'));
      $.ajax({
        url: url,
        type: 'json',
        data: '_token={{ csrf_token() }}',
        method: 'DELETE',
        error: () => {
          swal("Error!", "Failed to delete data. Please try again later.", "error");
        },
        success: () => {
          swal("Deleted!", "Data has been deleted.", "success");
          init();
        }
      });
    });
  }
});
</script>
@endsection
