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
            <div class="col-md-6">
              <div class="form-group">
                <label>Applicator</label>
                <select id="id_applicator" style="width: 100%;" name="id_applicator" class="form-control">
                  <option value="1" {!! (isset($inputs) && $inputs['id_applicator'] == 1) ? 'selected' : '' !!}>GOJEK</option>
                  <option value="2" {!! (isset($inputs) && $inputs['id_applicator'] == 2) ? 'selected' : '' !!}>GRAB</option>
                </select>
              </div>
              <div class="form-group">
                <label>Salesman</label>
                <select id="id_user" style="width: 100%;" name="id_user" class="form-control">
                  {!! $user_options !!}
                </select>
              </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <label>From</label>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-6">
                    <select id="from_month"  name="from_month" class="form-control">
                      {!! $from_month_options !!}
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select id="from_year"  name="from_year" class="form-control">
                      {!! $from_year_options !!}
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <label>To</label>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-6">
                    <select id="to_month"  name="to_month" class="form-control">
                      {!! $to_month_options !!}
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select id="to_year"  name="to_year" class="form-control">
                      {!! $to_year_options !!}
                    </select>
                  </div>
                </div>
            </div>
            <div class="col-md-4">
              
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
          <h3 class="box-title">Detail Report</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <div class="box-body">
            @if (!empty($incomecal) && count($incomecal) > 0)
              <h4 style="background-color:#f7f7f7; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;">
                {{ $applicator }}
              </h4>
              <div class="form-group">
              <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                  <strong>Name of Applicants</strong> : {{ $applicant }}
                </div>
                <div class="col-md-6 text-right">
                  {{ csrf_field() }}
                  @foreach($inputs as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
                  @endforeach
                  <button class="btn btn-primary" type="submit" name="download">
                    <i class="fa fa-download"></i> Download
                  </button>
                </div>
              </div>
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
              <table class="table table-bordered">
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
                  <td>{{ number_format(round($v->total_amount / $v->workdays), 0, '.', '.') }}</td>
                  <td>{{ number_format(round($v->total_expense / $v->workdays), 0, '.', '.') }}</td>
                  <td>{{ number_format(round(($v->total_amount - $v->total_expense) / $v->workdays), 0, '.', '.') }}</td>
                </tr>
                @php
                  $monthCount += 1;
                  $workDayTotal += $v->workdays;
                  $incomeGrossTotal += $v->total_amount;
                  $expenseTotal += $v->total_expense;
                  $incomeNetTotal += round($v->total_amount - $v->total_expense);
                  $incomeAvGrossTotal += round($v->total_amount / $v->workdays);
                  $expenseAvTotal += round($v->total_expense / $v->workdays);
                  $incomeAvNetTotal += round(($v->total_amount - $v->total_expense) / $v->workdays);
                @endphp
              @endforeach

              @php
                $incomeAvGrossMonth = round($incomeAvGrossTotal / $monthCount);
                $expenseAvGrossMonth = round($expenseAvTotal / $monthCount);
                $incomeAvNetMonth = round($incomeAvNetTotal / $monthCount);
              @endphp
                <tr>
                  <th colspan="3" width="30%">&nbsp;</th>
                  <th colspan="3" style="background: #F7F7F7">Average Monthly Income</th>
                  <th colspan="3" style="background: #F7F7F7">Average Daily Income</th>
                </tr>
                <tr>
                  <th rowspan="2" colspan="2" style="background: #F7F7F7; vertical-align : middle;text-align:center;" >Total</th>
                  <th style="background: #3B8DBC; color: #FFF">Actual Work Days</th>
                  <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                  <th style="background: #3B8DBC; color: #FFF">Expense</th>
                  <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                  <th style="background: #3B8DBC; color: #FFF">Income (Gross)</th>
                  <th style="background: #3B8DBC; color: #FFF">Expense</th>
                  <th style="background: #3B8DBC; color: #FFF">Income (Nett)</th>
                </tr>
                <tr>
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
                <tr>
                  <td>{{ $workDayTotal }}</td>
                  <td>{{ number_format($incomeGrossTotal, 0, '.', '.') }}</td>
                  <td>{{ number_format($expenseTotal, 0, '.', '.') }}</td>
                  <td>{{ number_format($incomeNetTotal, 0, '.', '.') }}</td>
                  <td>{{ number_format($incomeAvGrossTotal * 24, 0, '.', '.') }}</td>
                  <td>{{ number_format($expenseAvTotal * 24, 0, '.', '.') }}</td>
                  <td>{{ number_format($incomeAvNetTotal * 24, 0, '.', '.') }}</td>
                </tr>
              </table>
              </div>
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
  <link rel="stylesheet" href="{{ asset('/bower_components/select2/dist/css/select2.min.css') }}">
@endsection
@section('scripts')
  <script src="{{ asset('/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
  <script>
    $(function () {
      //Initialize Select2 Elements
      $('.select2').select2();
    });
  </script>
@endsection
