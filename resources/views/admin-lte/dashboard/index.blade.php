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
                @if (count($incomecal) > 0)
                    <table class="table table-bordered">
                        <tr>
                            <th>Driver Name</th>
                            <th>Driver ID Card</th>
                            <th>Applicator</th>
                            <th>PIC Sales</th>
                            <th>4 Weeks Income Gross</th>
                            <th>Last Submit Date</th>
                        </tr>
                        @foreach ($incomecal as $i => $v)
                            @php
                                $actualWorkDay4Weeks = (int) ceil($v->workdays / $workweeks[$v->driver_name][$v->driver_id_card]) * 4;
                                $incomeGross4Weeks = $v->workdays > 0 ? ceil(($v->total_amount / $v->workdays) * $actualWorkDay4Weeks) : 0
                            @endphp
                            <tr>
                                <td>{{ $v->driver_name }}</td>
                                <td>{{ $v->driver_id_card }}</td>
                                <td>{{ $k == 1 ? 'GO-JEK' : 'GRAB' }}</td>
                                <td>{{ $v->user->name }}</td>
                                <td>{{ number_format($incomeGross4Weeks, 0, '.', '.') }}</td>
                                <td>
                                @php
                                $datetime = new DateTime($v->submit_date);
                                $utcTime = new DateTimeZone('Asia/Jakarta');
                                $datetime->setTimezone($utcTime);
                                echo $datetime->format('Y-m-d H:i:s');
                                @endphp
                                </td>
                            </tr>
                        @endforeach
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
