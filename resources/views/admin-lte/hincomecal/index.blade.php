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
        <div class="col-md-3">
          <div class="form-group">
            <label for="driver_name">Driver's Name</label>
            <input class="form-control" type="text" id="driver_name" name="driver_name" />
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="driver_id_card">Driver's ID Card No.</label>
            <input class="form-control" type="text" id="driver_id_card" name="driver_id_card" />
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="id_applicator">Applicator</label>
            <select class="form-control" name="id_applicator" id="id_applicator">
              <option value="">ALL</option>
              <option value="1">GOJEK</option>
              <option value="2">GRAB</option>
            </select>
          </div>
        </div>
        <div class="col-md-3 text-right">
          <a class="btn btn-danger delete-bulk-action" href="#"><i class="fa fa-trash"></i> Delete Bulk</a>
          <a class="btn btn-primary" href="{{ route('upload.upload') }}"><i class="fa fa-plus"></i> Add Data</a> 
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
            <th width="13%"><input class="delete-bulk-all" type="checkbox" /></th>
            <th width="13%">Date</th>
            <th width="5%">Amount</th>
            <th width="10%">Driver's Name</th>
            <th width="10%">Applicator</th>
            <th width="15%">Trx Type</th>
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
<!-- JQuery UI -->
<link rel="stylesheet" href="{{ asset("/bower_components/jquery-ui/themes/base/jquery-ui.min.css") }}">
@endsection

@section('scripts')
<!-- Sweet Alert -->
<script src="{{ asset("/bower_components/bootstrap-sweetalert/dist/sweetalert.js") }}"></script>
<!-- JQuery UI -->
<script src="{{ asset("/bower_components/jquery-ui/jquery-ui.min.js") }}"></script>
<script>
$(document).ready(function() {
  // Autocomplete Driver's Name
  $("#driver_name").autocomplete({
    source: "{{ route('driver.searchname') }}",
    minLength: 2,
    select: function( event, ui ) {
      $("#driver_name").val(ui.item.driver_name);
      $("#driver_id_card").val(ui.item.driver_id_card);
      return false;
    }
  }).autocomplete( "instance" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .append( "<div>" + item.driver_name + " / " + item.driver_id_card + "</div>" )
      .appendTo( ul );
  };

  // Filter Data
  $("#driver_name, #driver_id_card").on('keyup', function() {
    init();
  });

  $('#id_applicator').on('change', function() {
    init();
  });

  // Init Datatable
  var bulkIds = [];
  function init() {
      $('.delete-bulk-all').prop('checked', false);
      bulkIds = [];
      let table = $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        destroy: true,
        ajax: {
            url: '{!! route('hincomecal.data') !!}',
            data: function (d) {
                d.driver_name = $('input[name=driver_name]').val();
                d.driver_id_card = $('input[name=driver_id_card]').val();
                d.id_applicator = $('select[name=id_applicator] option:selected').val();
            }
        },
        columns: [
          { data: 'id', searchable: false, orderable: false },
          { data: 'date_of_transaction', name: 'date_of_transaction' },
          { data: 'amount', name: 'amount' },
          { data: 'driver_name', name: 'driver_name' },
          { data: 'id_applicator', name: 'id_applicator' },
          { data: 'trans_type', name: 'trans_type' },
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
          initCheckAll();
          $(".delete").on('click', onDelete);
          $(".delete-bulk").on('click', onCheckDeleteBulk);
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

  function onCheckDeleteBulk(data) {
    var val = $(this).val();
    if ($(this).is(':checked')) {
      bulkIds.push(val);
    } else {
      bulkIds.splice( $.inArray(val, bulkIds), 1 );
    }
  }
  
  function initCheckAll() {
    $('.delete-bulk-all').unbind('click');

    $('.delete-bulk-all').on('click', function() {
      if ($(this).is(':checked')) {
        $('.delete-bulk').prop('checked', true);
        $('.delete-bulk').each(function() {
          bulkIds.push($(this).val());
        });
      } else {
        $('.delete-bulk').prop('checked', false);
        $('.delete-bulk').each(function() {
          bulkIds.splice( $.inArray($(this).val(), bulkIds), 1 );
        });
      }
    });
  }

  $('.delete-bulk-action').on('click', function(e) {
    e.preventDefault();

    if (bulkIds.length === 0) {
      return swal("Error!", "Please choose at least 1 data to be deleted.", "error");
    }

    return swal({
      title: "Are you sure?",
      text: "Your will not be able to recover this data!",
      type: "warning",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes, delete it!",
      closeOnConfirm: false
    }, () => {
      let url = '{{ route("hincomecal.destroy.bulk") }}';
      url = url.replace(':id', $(this).data('id'));
      $.ajax({
        url: url,
        type: 'json',
        data: '_token={{ csrf_token() }}&ids=' + bulkIds.join(','),
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
  });
});
</script>
@endsection
