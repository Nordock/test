<?php

namespace App\Exports;

use App\Models\HIncomecal;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromView, ShouldAutoSize
{
    public $data;

    public function __construct($data)
    {
      $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.report', $this->data);
    }
}
