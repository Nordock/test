<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Validator;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin-lte.upload.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,png,jpg,pdf,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                if ($file->getClientOriginalExtension() !== 'pdf') {
                    $type = 'image';
                    $data = (new TesseractOCR($file
                    ->getRealPath()))
                    ->tessdataDir(config('constants.tesseract_testdata_dir'))
                    ->psm(6)
                    ->run();
                    
                    $result = $this->parseImage($data);
                } else {
                    $type = 'pdf';
                    $data = (new Pdf(config('constants.pdftotext')))
                    ->setPdf($file->getRealPath())
                    ->setOptions(['layout', 'r 96'])
                    ->text();

                    $result = $this->parsePdfReport($data);
                }
            }

            return response()->json([
                'type' => $type,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Parse pdf file
     */
    public function parsePdfReport($data)
    {
        $result = [];
        $pattern = "/Rincian Perjalanan/";
        $split = preg_split($pattern, $data);
        if (count($split) < 2) {
            throw new \Exception('Invalid file. Could not read the pdf!');
        }
        
        $summaries = $split[0];
        $details = $split[1];
        
        // ------------------------- Parse summaries ------------------------------ //
        $splitSummaries = explode("\n", $summaries);
        $splitSummaries = array_values(array_filter($splitSummaries, 'strlen'));
        $contentKey1 = [
            'date' => 0,
            'row_1' => 6,
            'row_2' => 7,
            'row_3' => 8,
            'row_4' => 9,
        ];

        $tipExist = false;
        if (strpos($splitSummaries[$contentKey1['row_2']], 'Tip') !== false) {
            $tipExist = true;
        }
        
        preg_match('/\d+[st|nd|rd|th]+ [aA-zZ]+, \d{4}+/', $splitSummaries[$contentKey1['date']], $dates);
        preg_match_all('/\d+|[aA-zZ]+|\d{4}+/', $dates[0], $dates);

        // Amount & Commission
        preg_match_all('/\d+.\d+.\d+|\d+.\d+|\d+/', $splitSummaries[$contentKey1['row_1']], $row_1);
        
        // Tip / Incentive & Rental Cost
        preg_match_all('/\d+.\d+.\d+|\d+.\d+|\d+/', $splitSummaries[$contentKey1['row_2']], $row_2);
        
        // Other Income & Adjustment
        preg_match_all('/\d+.\d+.\d+|\d+.\d+|\d+/', $splitSummaries[$contentKey1['row_3']], $row_3);

        // Adjustment if tips exist
        preg_match_all('/\d+.\d+.\d+|\d+.\d+|\d+/', $splitSummaries[$contentKey1['row_4']], $row_4);
        
        $amount = preg_replace('/\./', '', $row_1[0][0]);
        $commission = preg_replace('/\./', '', $row_1[0][1]);
        $rentalCost = preg_replace('/\./', '', $row_2[0][1]);
        $adjustment = preg_replace('/\./', '', $row_3[0][1]);
        $incentive = preg_replace('/\./', '', $row_2[0][0]);
        $otherIncome = preg_replace('/\./', '', $row_3[0][0]);
        
        if ($tipExist) {
            $incentive = preg_replace('/\./', '', $row_3[0][0]);
            $otherIncome = preg_replace('/\./', '', $row_4[0][0]);
        }

        // -------------------------- Parse details ------------------------------- //
        $detailPattern = "/([a-zA-Z]+, [0-9]+ [Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember]+)/";
        $splitDetail = preg_split($detailPattern, $details, NULL, PREG_SPLIT_DELIM_CAPTURE);

        // Get trx date to index 0
        foreach ($splitDetail as $k => $value) {
            if (preg_match($detailPattern, $value)) {
                \array_splice($splitDetail, 0, $k);
                break;
            }
        }

        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'Mei' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Agu' => '08',
            'Sep' => '09',
            'Okt' => '10',
            'Nov' => '11',
            'Des' => '12',
        ];

        $trx = [];
        $day = substr("0". $dates[0][0], -2, 2);
        $trx['date'] = "{$dates[0][3]}-{$months[$dates[0][2]]}-{$day}";
        $trx['amount'] = $amount;
        $trx['trx_type'] = "-";
        $trx['trx_value'] = 0;
        $trx['trx_cost_value'] = 0;
        $trx['incentive'] = $incentive;
        $trx['other_income'] = $otherIncome;
        $trx['commission'] = $commission;
        $trx['rental_cost'] = $rentalCost;
        $trx['adjustment'] = $adjustment;
        $trx['workdays'] = count($splitDetail) / 2;

        return [$trx];
    }

    public function parseImageText($data)
    {
        
    }

    /**
     * Parse image file
     */
    public function parseImage($data)
    {
        // Search date format
        $parsedData = [];

        // Split by dates (1 date means 1 transaction)
        $pattern = "/(\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2})/";
        $matchDates = preg_split($pattern, $data, NULL, PREG_SPLIT_DELIM_CAPTURE);
        if (count($matchDates) < 1) {
            throw new \Exception('Invalid file. Could not read the image!');
        }

        $result = [];
        $trx = [];
        // Get trx date to index 0
        foreach ($matchDates as $k => $value) {
            if (preg_match($pattern, $value)) {
                \array_splice($matchDates, 0, $k);
                break;
            }
        }

        // Set up data format
        $dataLength = count($matchDates);
        $result = [];
        $test = [];
        for ($i = 0; $i < $dataLength; $i += 2) {
            $trx = [];
            if (!isset($matchDates[$i + 1])) {
                continue;
            }
            
            // Split content by newline
            $content = $matchDates[$i + 1];
            $splitContent = explode("\n", $content);
            $splitContent = array_values(array_filter($splitContent, 'strlen'));
            $splitCount = count($splitContent);
            $contentKey = [
                'amount' => 0,
                'trx_type' => 1,
                'type' => 2,
                'desc' => 3,
                'balance' => 4,
            ];

            // Check if the section screenshot of this transaction full taken
            if ($splitCount < 4) {
                continue;
            }

            // Get amount to index 0
            foreach ($splitContent as $k => $value) {
                if (preg_match('/Rp+/', $value)) {
                    \array_splice($splitContent, 0, $k);
                    break;
                }
            }

            // Check if there is tip type
            $type = $splitContent[$contentKey['type']];
            $description = $splitContent[$contentKey['desc']];
            if (strpos($splitContent[$contentKey['type']], 'Tip payment for order') !== false) {
                $balanceFrom = $splitContent[$contentKey['desc']];
                $description = '-';
                $type = $splitContent[$contentKey['type']];
            } else if (strpos($splitContent[$contentKey['desc']], 'Total Saldo Akhir') !== false) {
                // Check desc position
                $description = '-';
                $balanceFrom = $splitContent[$contentKey['desc']];
            } else if (!isset($splitContent[$contentKey['balance']])) {
                continue;
            } else {
                $balanceFrom = $splitContent[$contentKey['balance']];
            }
            
            // Remove Rp and '.' from amount & balance & get DEBIT/CREDIT words
            preg_match_all('!\d+!', $splitContent[$contentKey['amount']], $amount);
            preg_match_all('!\d+!', $balanceFrom, $balance);
            preg_match('/DEBIT|CREDIT/', $splitContent[$contentKey['trx_type']], $trxType);
            
            // Exclude withdraw, topup, cicilan, premi, initial payment
            if (
                stripos($type, 'withdraw') !== false || 
                stripos($type, 'topup') !== false ||
                stripos($type, 'top-up') !== false ||
                stripos($type, 'cicilan') !== false ||
                stripos($type, 'premi') !== false ||
                stripos($type, 'initial') !== false
            ) {
                continue;
            }

            // Standarized type for tips transaction
            if (stripos($type, 'tip') !== false) {
                $type = 'TIP';
            }

            // Standarized type for pembayaran transaction
            if (stripos($type, 'pembayaran bonus') !== false) {
                $type = 'PEMBAYARAN BONUS';
            }

            // Standarized type for go bluebird transaction
            if (stripos($type, 'bluebird') !== false) {
                $type = 'GO-BIRDCOMBO';
            }

            $trx['date'] = $matchDates[$i];
            $trx['amount'] = implode('', $amount[0]);
            $trx['type'] = $type;
            $trx['trx_type'] = "$trxType[0] ($type)";
            $trx['trx_value'] = $this->calculateTrxValue($trxType[0], $type);
            $trx['trx_cost_value'] = $this->calculateTrxCostValue($trxType[0], $type);
            $trx['incentive'] = 0;
            $trx['other_income'] = 0;
            $trx['commission'] = 0;
            $trx['rental_cost'] = 0;
            $trx['adjustment'] = 0;
            $trx['desc'] = $description;
            $trx['workdays'] = 1;
            $trx['balance'] = implode('', $balance[0]);

            $result[] = $trx;
        }
        
        return $result;
    }

    /**
     * Calculate transaction value.
     */
    public function calculateTrxValue ($trxType, $type)
    {
        $template = [
            'DEBIT' => [
                'GO-CAR' => 5,
                'GO-BIRDCOMBO' => 5,
                'WITHDRAWAL' => 0
            ],
            'CREDIT' => [
                'TOPUP' => 0,
                'GO-CAR' => 1.25,
                'PEMBAYARAN BONUS' => 1,
                'TIP' => 1,
                'GO-BIRDCOMBO' => 1.25
            ]
        ];

        if (!isset($template[$trxType][$type])) {
            return 0;
        }

        return $template[$trxType][$type];
    }

    /**
     * Calculate transaction cost value.
     */
    public function calculateTrxCostValue ($trxType, $type)
    {
        $template = [
            'DEBIT' => [
                'GO-CAR' => 0.2,
                'GO-BIRDCOMBO' => 0.2,
                'WITHDRAWAL' => 0
            ],
            'CREDIT' => [
                'TOPUP' => 0,
                'GO-CAR' => 0.2,
                'PEMBAYARAN BONUS' => 0,
                'TIP' => 0,
                'GO-BIRDCOMBO' => 0.2
            ]
        ];

        if (!isset($template[$trxType][$type])) {
            return 0;
        }

        return $template[$trxType][$type];
    }
}
