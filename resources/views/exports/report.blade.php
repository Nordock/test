@if (!empty($incomecals) && count($incomecals) > 0)
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
            <td colspan="3" >Monthly Income</td>
            <td colspan="3" >Average Daily Income</td>
          </tr>
          <tr>
            <td colspan="2" rowspan="2" style="background: #F7F7F7; vertical-align : middle;text-align:center;" >{{ date('M, Y', strtotime($v->year.'-'.$v->month.'-01')) }}</td>
            <td >Actual Work Days</td>
            <td >Income (Gross)</td>
            <td >Expense</td>
            <td >Income (Nett)</td>
            <td >Income (Gross)</td>
            <td >Expense</td>
            <td >Income (Nett)</td>
          </tr>
          <tr>
            <td>{{ $v->workdays }}</td>
            <td>{{ 'Rp. '. number_format($v->total_amount, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($v->total_expense, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format(round($v->total_amount - $v->total_expense), 0, '.', '.') }}</td>
            <td>{{ $v->workdays > 0 ? 'Rp. '. number_format(round($v->total_amount / $v->workdays), 0, '.', '.') : 0 }}</td>
            <td>{{ $v->workdays > 0 ? 'Rp. '. number_format(round($v->total_expense / $v->workdays), 0, '.', '.') : 0 }}</td>
            <td>{{ $v->workdays > 0 ? 'Rp. '. number_format(round(($v->total_amount - $v->total_expense) / $v->workdays), 0, '.', '.') : 0 }}</td>
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
            <th colspan="3" >Average Monthly Income</th>
            <th colspan="3" >Average Daily Income</th>
          </tr>
          <tr>
            <th rowspan="2" style="background: #F7F7F7; vertical-align : middle;text-align:center;" >Total</th>
            <th >Work weeks</th>
            <th >Actual Work Days</th>
            <th >Income (Gross)</th>
            <th >Expense</th>
            <th >Income (Nett)</th>
            <th >Income (Gross)</th>
            <th >Expense</th>
            <th >Income (Nett)</th>
          </tr>
          <tr>
            <td>{{ $workweeks[$applicator] }}</td>
            <td>{{ $workDayTotal }}</td>
            <td>{{ 'Rp. '. number_format($incomeGrossTotal, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($expenseTotal, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($incomeNetTotal, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($incomeAvGrossMonth, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($expenseAvGrossMonth, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($incomeAvNetMonth, 0, '.', '.') }}</td>
          </tr>
          <tr>
            <th colspan="2" width="30%">&nbsp;</th>
            <th colspan="4" >Focast Monthly Income</th>
            <th colspan="3" >Focast Monthly Income (24 days)</th>
          </tr>
          <tr>
            <th rowspan="2" colspan="2" style="text-align:center;" >
              Estimation<br />
              4 Weeks
            </th>
            <th >Actual Work Days</th>
            <th >Income (Gross)</th>
            <th >Expense</th>
            <th >Income (Nett)</th>
            <th >Income (Gross)</th>
            <th >Expense</th>
            <th >Income (Nett)</th>
          </tr>
          @php
            $actualWorkDays4Weeks = $workweeks[$applicator] > 0 ? ((int) ceil(($workDayTotal / $workweeks[$applicator]) * 4)) : 0;
            $incomeGross4Weeks = $workDayTotal > 0 ? ($incomeGrossTotal / $workDayTotal) * $actualWorkDays4Weeks : 0;
            $expense4Weeks = $workDayTotal > 0 ? ($expenseTotal / $workDayTotal) * $actualWorkDays4Weeks : 0;
            $incomeNett4Weeks = $incomeGross4Weeks - $expense4Weeks;
          @endphp
          <tr>
            <td>{{ $actualWorkDays4Weeks }}</td>
            <td>{{ 'Rp. '. number_format($incomeGross4Weeks, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($expense4Weeks, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($incomeNett4Weeks, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($incomeAvGrossMonth * 24, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format($expenseAvGrossMonth * 24, 0, '.', '.') }}</td>
            <td>{{ 'Rp. '. number_format(($incomeAvGrossMonth * 24) - ($expenseAvGrossMonth * 24), 0, '.', '.') }}</td>
          </tr>
        </table>
      </div>
    @endif
  @endforeach
@endif