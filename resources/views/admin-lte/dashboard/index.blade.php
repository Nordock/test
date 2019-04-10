@extends('admin-lte.layouts.template')

@section('content')

<div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Report in the last 3 months</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <div class="box-body">
            @if (!empty($incomecals) && count($incomecals) > 0)
              @foreach ($incomecals as $k => $incomecal)
                <h4 style="background-color:#f7f7f7; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;">
                    {{ $k == 1 ? 'GOJEK' : 'GRAB' }}
                </h4>
                <div class="form-group">
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
                @if (count($incomecal) > 0)
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
                @else
                    <div class="text-center">No Data</div>
                @endif
                </div>
                @endforeach
            @else
            <div class="form-group text-center">
              Empty Data
            </div>
            @endif
        </div>
      </div>
  </div>      
</div>
@endsection
