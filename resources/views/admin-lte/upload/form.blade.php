@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    Upload Data
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li><a class="active">Upload Data</a></li>
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
<form role="form" method="POST" enctype="multipart/form-data" action="{{ route('hincomecal.store') }}">
  {{ csrf_field() }}
  <div class="row">
    <!-- left column -->
    <div class="col-md-12">
      <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Upload</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div class="form-group">
            <label for="vendor">Applicator</label>
            <select class="form-control" name="vendor" id="vendor">
              <option value="GOJEK">GOJEK</option>
              <option value="GRAB">GRAB</option>
            </select>
          </div>
          <div class="form-group">
            <label for="photo">Files</label>
            <div class="input-group">
              <!-- <input type="file" name="file" /> -->
              <a class="btn btn-primary" href="#" data-toggle="modal" data-target="#modal-box">Choose Files</a>
            </div>
            <p class="help-block">Format file allowed : png, jpg, pdf.</p>
          </div>
          <div class="form-group">
            <table class="table-row table table-bordered" id="GOJEK">
              <thead>
                <tr>
                  <th width="13%">Date</th>
                  <th width="13%">Trx Type</th>
                  <th width="5%">Amount</th>
                  <th width="8%">Trx Value</th>
                  <th width="10%">Trx Cost Value</th>
                  <th width="5%">Workdays</th>
                  <th width="5%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
            <table class="hidden table-row table table-bordered" id="GRAB">
              <thead>
                <tr>
                  <th width="13%">Date</th>
                  <th width="5%">Amount</th>
                  <th width="8%">Incentive</th>
                  <th width="10%">Other Income</th>
                  <th width="8%">Commission</th>
                  <th width="8%">Rental Cost</th>
                  <th width="8%">Adjustment</th>
                  <th width="5%">Workdays</th>
                  <th width="5%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
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

@section('modals')
<div class="modal fade" id="modal-box">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Import Data</h4>
      </div>
      <div class="modal-body">
        <form method="post" action="" id="importdropzone" class="dropzone" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="fallback">
              <input name="file" type="file" multiple />
          </div>
        </form>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection

@section('styles')
<!-- Datepicker -->
<link rel="stylesheet" href="{{ asset("/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css") }}">
<!-- Sweet Alert -->
<link rel="stylesheet" href="{{ asset("/bower_components/bootstrap-sweetalert/dist/sweetalert.css") }}">
<!-- Dropzone -->
<link href="{{ asset('assets/vendor/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
<!-- Datepicker -->
<script src="{{ asset("/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js") }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset("/bower_components/bootstrap-sweetalert/dist/sweetalert.js") }}"></script>
<!-- Dropzone -->
<script src="{{ asset('assets/vendor/dropzone/dropzone.min.js') }}"></script>

<script>
function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

$(document).ready(function() {
  $('#vendor').on('change', function() {
    $('.table-row').addClass('hidden');
    $('#' + $(this).val()).removeClass('hidden');
    $('.table-row tbody').empty();
  });

  var fieldNames = [
    'date',
    'trx_type',
    'amount',
    'trx_value',
    'trx_cost_value',
    'incentive',
    'other_income',
    'commission',
    'rental_cost',
    'adjustment',
    'workdays',
  ];

  // Action Remove
  $('.table-row').on('click', '.remove-row', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  // Action Edit
  $('.table-row').on('click', '.edit-row', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    
    $(this).addClass('hidden');
    $(`.text-${id}`).addClass('hidden');
    $(`.remove-${id}`).addClass('hidden');
    $(`.row-${id}`).removeClass('hidden');
    $(`.save-${id}`).removeClass('hidden');
  });

  // Action Save
  $('.table-row').on('click', '.save-row', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    $(this).addClass('hidden');
    $(`.row-${id}`).addClass('hidden');
    $(`.text-${id}`).removeClass('hidden');
    $(`.edit-${id}`).removeClass('hidden');
    $(`.remove-${id}`).removeClass('hidden');

    var fieldLength = fieldNames.length;
    var fieldSelector
    for (var x = 0; x < fieldLength; x += 1) {
      $(`.text-${fieldNames[x]}-${id}`).text($(`.input-${fieldNames[x]}-${id}`).val());
    }
  });

  Dropzone.autoDiscover = false;
  //----- DROPZONE -----//
  var dataRow = [];
  var appendTableGojek = (dataRow) => {
    var dataLength = dataRow.length;
    for (var i = 0; i < dataLength; i += 1) {
      var rowLength = dataRow[i].length;
      for (var a = 0; a < rowLength; a += 1) {
        var uuid = uuidv4();
        var tableData = '';
        var fieldNames = [
          'date',
          'trx_type',
          'amount',
          'trx_value',
          'trx_cost_value',
          'workdays',
        ];
        var fieldLength = fieldNames.length;
        for (var x = 0; x < fieldLength; x += 1) {
          tableData += `
            <td>
              <span class="text-${fieldNames[x]}-${uuid} text-${uuid}">${dataRow[i][a][fieldNames[x]]}</span>
              <input type="text" required name="${fieldNames[x]}[]" class="input-${fieldNames[x]}-${uuid} hidden row-${uuid} form-control" value="${dataRow[i][a][fieldNames[x]]}" />
            </td>
          `;
        }

        $('#GOJEK tbody:last-child').append(`
          <tr>
            ${tableData}
            <td class="text-center">
              <a class="save-row save-${uuid} hidden" data-id="${uuid}" href="#"><i class="fa fa-check"></i></a>
              <a class="edit-row edit-${uuid}" data-id="${uuid}" href="#"><i class="fa fa-edit"></i></a>
              <a class="remove-row remove-${uuid}" href="#"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
        `);
      }
    }
  };

  var appendTableGrab = (dataRow) => {
    var dataLength = dataRow.length;
    for (var i = 0; i < dataLength; i += 1) {
      var rowLength = dataRow[i].length;
      for (var a = 0; a < rowLength; a += 1) {
        var uuid = uuidv4();
        var tableData = '';
        var fieldNames = [
          'date',
          'amount',
          'incentive',
          'other_income',
          'commission',
          'rental_cost',
          'adjustment',
          'workdays',
        ];
        var fieldLength = fieldNames.length;
        for (var x = 0; x < fieldLength; x += 1) {
          tableData += `
            <td>
              <span class="text-${fieldNames[x]}-${uuid} text-${uuid}">${dataRow[i][a][fieldNames[x]]}</span>
              <input type="text" required name="${fieldNames[x]}[]" class="input-${fieldNames[x]}-${uuid} hidden row-${uuid} form-control" value="${dataRow[i][a][fieldNames[x]]}" />
            </td>
          `;
        }

        $('#GRAB tbody:last-child').append(`
          <tr>
            ${tableData}
            <td class="text-center">
              <a class="save-row save-${uuid} hidden" data-id="${uuid}" href="#"><i class="fa fa-check"></i></a>
              <a class="edit-row edit-${uuid}" data-id="${uuid}" href="#"><i class="fa fa-edit"></i></a>
              <a class="remove-row remove-${uuid}" href="#"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
        `);
      }
    }
  };

  var myDropzone = new Dropzone("form#importdropzone", {
    url: "{{ route('upload.upload') }}",
    acceptedFiles: ".png, .jpg, .png, .jpeg, .pdf",
    error: function(file, response) {
      $('#modal-box').modal('hide');
      myDropzone.removeAllFiles();
      swal("Error!", response || "Failed to upload data. Please try again later.", "error");
    },
    success: function(file, response) {
      var type = response.type;
      dataRow.push(response.data);
      if (type === 'pdf') {
        appendTableGrab(dataRow);
      } else {
        appendTableGojek(dataRow);
      }

      dataRow = [];
      this.on("queuecomplete", function (file) {
        $('#modal-box').modal('hide');
        myDropzone.removeAllFiles();
        swal("Uploaded!", "Data transaction has been uploaded.", "success");
      });
    }
  });
});
</script>
@endsection
