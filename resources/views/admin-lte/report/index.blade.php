@extends('admin-lte.layouts.template')

@section('title')
<section class="content-header">
  <h1>
    Report
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">Report</li>
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

<div class="row">
  <div class="col-xs-12">
    <form method="POST" action="{{ route('report.report') }}">
      {{ csrf_field() }}
      <div class="box">
        <!-- /.box-header -->
        <div class="box-header with-border">
          <h3 class="box-title">Filter Report</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="driver_name">Driver's Name</label>
                <input class="form-control" type="text" id="driver_name" name="driver_name" value="{{ $inputs['driver_name'] or old('driver_name') }}"/>
              </div>
              <div class="form-group">
                <label for="driver_id_card">Driver's ID Card No.</label>
                <input class="form-control" type="text" id="driver_id_card" name="driver_id_card" value="{{ $inputs['driver_id_card'] or old('driver_id_card') }}"/>
              </div>
            </div>
          </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <div class="row">
            <div class="col-md-12 text-right">
              <input type="submit" class="btn btn-primary" value="Filter" name="submit" />
            </div>
          </div>
        </div>
      </div>
    <!-- /.box -->
    </form>
  </div>
  <!-- /.col -->

  <div class="col-xs-12">
    <form method="POST" action="{{ route('report.download') }}">
      {{ csrf_field() }}
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Detail Report Last 3 Months</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <div class="box-body">
            @if (!empty($incomecals) && count($incomecals) > 0)
            <div class="row" style="margin-bottom: 20px;">
              <div class="col-md-12 text-right">
                  {{ csrf_field() }}
                  @foreach($inputs as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
                  @endforeach
                  <button class="btn btn-primary" type="submit" name="download">
                    <i class="fa fa-download"></i> Download
                  </button>
                </div>
              </div>
              @foreach ($incomecals as $applicator => $incomecal)
                @if (count($incomecal) > 0)
                  <h4 style="background-color:#f7f7f7; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;">
                    {{ $applicator == 1 ? 'GO-JEK' : 'GRAB' }}
                  </h4>
                  <div class="form-group" style="overflow: auto">
                    @php
                      $workDayTotal = 0;
                      $incomeGrossTotal = 0;
                      $expenseTotal = 0;
                      $incomeNetTotal = 0;
                      $incomeAvGrossTotal = 0;
                      $expenseAvTotal = 0;
                      $incomeAvNetTotal = 0;
                      $monthCount = 0;
                    @endphp
                    <table class="table table-bordered responsive">
                    @foreach ($incomecal as $k => $v)
                      <tr>
                        <td colspan="3" width="30%">&nbsp;</td>
                        <td colspan="3" style="background: #F7F7F7">Monthly Income</td>
                        <td colspan="3" style="background: #F7F7F7">Average Daily Income</td>
                      </tr>
                      <tr>
                        <td colspan="2" rowspan="2" style="background: #F7F7F7; vertical-align : middle;text-align:center;" >{{ date('M, Y', strtotime($v->year.'-'.$v->month.'-01')) }}</td>
                        <td style="background: #3B8DBC; color: #FFF">Actual Work Days</td>
                        <td style="background: #3B8DBC; color: #FFF">Income (Gross)</td>
                        <td style="background: #3B8DBC; color: #FFF">Expense</td>
                        <td style="background: #3B8DBC; color: #FFF">Income (Nett)</td>
                        <td style="background: #3B8DBC; color: #FFF">Income (Gross)</td>
                        <td style="background: #3B8DBC; color: #FFF">Expense</td>
                        <td style="background: #3B8DBC; color: #FFF">Income (Nett)</td>
                      </tr>
                      <tr>
                        <td>{{ $v->workdays }}</td>
                        <td>{{ number_format($v->total_amount, 0, '.', '.') }}</td>
                        <td>{{ number_format($v->total_expense, 0, '.', '.') }}</td>
                        <td>{{ number_format(round($v->total_amount - $v->total_expense), 0, '.', '.') }}</td>
                        <td>{{ $v->workdays > 0 ? number_format(round($v->total_amount / $v->workdays), 0, '.', '.') : 0 }}</td>
                        <td>{{ $v->workdays > 0 ? number_format(round($v->total_expense / $v->workdays), 0, '.', '.') : 0 }}</td>
                        <td>{{ $v->workdays > 0 ? number_format(round(($v->total_amount - $v->total_expense) / $v->workdays), 0, '.', '.') : 0 }}</td>
                      </tr>
                      @php
                        $monthCount += 1;
                        $workDayTotal += $v->workdays;
                        $incomeGrossTotal += $v->total_amount;
                        $expenseTotal += $v->total_expense;
                        $incomeNetTotal += round($v->total_amount - $v->total_expense);
                        $incomeAvGrossTotal += $v->workdays > 0 ? round($v->total_amount / $v->workdays) : 0;
                        $expenseAvTotal += $v->workdays > 0 ? round($v->total_expense / $v->workdays) : 0;
                        $incomeAvNetTotal += $v->workdays > 0 ? round(($v->total_amount - $v->total_expense) / $v->workdays) : 0;
                      @endphp
                    @endforeach
                    @php
                      $incomeAvGrossMonth = $incomeAvGrossTotal > 0 ? ceil($incomeAvGrossTotal / $monthCount) : 0;
                      $expenseAvGrossMonth = $expenseAvTotal > 0 ? ceil($expenseAvTotal / $monthCount) : 0;
                      $incomeAvNetMonth = $incomeAvNetTotal > 0 ? ceil($incomeAvNetTotal / $monthCount) : 0;
                    @endphp
                      <tr>
                        <th colspan="3" width="30%">&nbsp;</th>
                        <th colspan="3" style="background: #F7F7F7">Average Monthly Income</th>
                        <th colspan="3" style="background: #F7F7F7">Average Daily Income</th>
                      </tr>
                      <tr>
                        <th rowspan="2" style="background: #F7F7F7; vertical-align : middle;text-align:center;" >Total</th>
                        <th style="background: #3B8DBC; color: #FFF">Work weeks</th>
                        <th style="background: #3B8DBC; color: #FFF">Actual Work Days</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                        <th style="background: #3B8DBC; color: #FFF">Expense</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                        <th style="background: #3B8DBC; color: #FFF">Expense</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                      </tr>
                      <tr>
                        <td>{{ $workweeks[$applicator] }}</td>
                        <td>{{ $workDayTotal }}</td>
                        <td>{{ number_format($incomeGrossTotal, 0, '.', '.') }}</td>
                        <td>{{ number_format($expenseTotal, 0, '.', '.') }}</td>
                        <td>{{ number_format($incomeNetTotal, 0, '.', '.') }}</td>
                        <td>{{ number_format($incomeAvGrossMonth, 0, '.', '.') }}</td>
                        <td>{{ number_format($expenseAvGrossMonth, 0, '.', '.') }}</td>
                        <td>{{ number_format($incomeAvNetMonth, 0, '.', '.') }}</td>
                      </tr>
                      <tr>
                        <th colspan="2" width="30%">&nbsp;</th>
                        <th colspan="4" style="background: #F7F7F7">Focast Monthly Income</th>
                        <th colspan="3" style="background: #F7F7F7">Focast Monthly Income (24 days)</th>
                      </tr>
                      <tr>
                        <th rowspan="2" colspan="2" style="color: red; background: #F7F7F7;vertical-align : middle;text-align:center;" >
                          Estimation<br />
                          4 Weeks
                        </th>
                        <th style="background: #3B8DBC; color: #FFF">Actual Work Days</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                        <th style="background: #3B8DBC; color: #FFF">Expense</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                        <th style="background: #3B8DBC; color: #FFF">Expense</th>
                        <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                      </tr>
                      @php
                        $actualWorkDays4Weeks = $workweeks[$applicator] > 0 ? ((int) ceil(($workDayTotal / $workweeks[$applicator]) * 4)) : 0;
                        $incomeGross4Weeks = $workDayTotal > 0 ? ($incomeGrossTotal / $workDayTotal) * $actualWorkDays4Weeks : 0;
                        $expense4Weeks = $workDayTotal > 0 ? ($expenseTotal / $workDayTotal) * $actualWorkDays4Weeks : 0;
                        $incomeNett4Weeks = $incomeGross4Weeks - $expense4Weeks;
                      @endphp
                      <tr>
                        <td>{{ $actualWorkDays4Weeks }}</td>
                        <td>{{ number_format($incomeGross4Weeks, 0, '.', '.') }}</td>
                        <td>{{ number_format($expense4Weeks, 0, '.', '.') }}</td>
                        <td>{{ number_format($incomeNett4Weeks, 0, '.', '.') }}</td>
                        <td>{{ number_format($incomeAvGrossMonth * 24, 0, '.', '.') }}</td>
                        <td>{{ number_format($expenseAvGrossMonth * 24, 0, '.', '.') }}</td>
                        <td>{{ number_format(($incomeAvGrossMonth * 24) - ($expenseAvGrossMonth * 24), 0, '.', '.') }}</td>
                      </tr>
                    </table>
                  </div>
                @endif
              @endforeach
            @else
            <div class="form-group text-center">
              Empty Data
            </div>
            @endif
        </div>
      </div>
    </form>
  </div>      
</div>
<!-- /.row -->
@endsection

@section('styles')
  <!-- Select 2 -->
  <link rel="stylesheet" href="{{ asset('/bower_components/select2/dist/css/select2.min.css') }}">
  <!-- JQuery UI -->
  <link rel="stylesheet" href="{{ asset("/bower_components/jquery-ui/themes/base/jquery-ui.min.css") }}">
@endsection

@section('scripts')
  <!-- Select 2 -->
  <script src="{{ asset('/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
  <!-- JQuery UI -->
  <script src="{{ asset("/bower_components/jquery-ui/jquery-ui.min.js") }}"></script>
  <script>
    $(function () {
      //Initialize Select2 Elements
      $('.select2').select2();

      //Autocomplete Driver's Name
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
    });
  </script>
@endsection
