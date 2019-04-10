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
    <td>Actual Work Days</td>
    <td>Income (Gross)</td>
    <td>Expense</td>
    <td>Income (Nett)</td>
    <td>Income (Gross)</td>
    <td>Expense</td>
    <td>Income (Nett)</td>
  </tr>
  <tr>
    <td>{{ $v->workdays }}</td>
    <td>{{ "Rp. ".number_format($v->total_amount, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($v->total_expense, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format(round($v->total_amount - $v->total_expense), 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format(round($v->total_amount / $v->workdays), 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format(round($v->total_expense / $v->workdays), 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format(round(($v->total_amount - $v->total_expense) / $v->workdays), 0, '.', '.')."" }}</td>
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
    <th>Actual Work Days</th>
    <th>Income (Gross)</th>
    <th>Expense</th>
    <th>Income (Nett)</th>
    <th>Income (Gross)</th>
    <th>Expense</th>
    <th>Income (Nett)</th>
  </tr>
  <tr>
    <td>{{ $workDayTotal }}</td>
    <td>{{ "Rp. ".number_format($incomeGrossTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($expenseTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeNetTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeAvGrossMonth, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($expenseAvGrossMonth, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeAvNetMonth, 0, '.', '.')."" }}</td>
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
    <th>Actual Work Days</th>
    <th>Income (Gross)</th>
    <th>Expense</th>
    <th>Income (Nett)</th>
    <th>Income (Gross)</th>
    <th>Expense</th>
    <th>Income (Nett)</th>
  </tr>
  <tr>
    <td>{{ $workDayTotal }}</td>
    <td>{{ "Rp. ".number_format($incomeGrossTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($expenseTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeNetTotal, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeAvGrossTotal * 24, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($expenseAvTotal * 24, 0, '.', '.')."" }}</td>
    <td>{{ "Rp. ".number_format($incomeAvNetTotal * 24, 0, '.', '.')."" }}</td>
  </tr>
</table>