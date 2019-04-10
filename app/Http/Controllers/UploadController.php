<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;
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
                    $data = \OCR::scan($file->getRealPath());
                    $result = $this->parseImage($data);
                } else {
                    $type = 'pdf';
                    $data = Pdf::getText($file->getRealPath(), config('constants.pdftotext'));
                    $result = $this->parsePdfSummaries($data);
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
    public function parsePdfSummaries($data)
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
        $contentKey = [
            'date' => 2,
            'income' => 4,
            'outcome' => 6
        ];
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

        preg_match_all('/\d+|[aA-zZ]+|\d{4}+/', $splitSummaries[$contentKey['date']], $dates);
        preg_match_all('/[\d+.\d+|\d+]+/', $splitSummaries[$contentKey['income']], $incomes);
        preg_match_all('/[-\d+.\d+|\d+]+/', $splitSummaries[$contentKey['outcome']], $outcomes);

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

        $trx = [];
        $day = substr("0". $dates[0][0], -2, 2);
        $trx['date'] = "{$dates[0][3]}-{$months[$dates[0][2]]}-{$day}";
        $trx['amount'] = preg_replace('/\./', '', $incomes[0][0]);
        $trx['trx_type'] = "-";
        $trx['trx_value'] = 0;
        $trx['trx_cost_value'] = 0;
        $trx['incentive'] = preg_replace('/\./', '', $incomes[0][1]);
        $trx['other_income'] = preg_replace('/\./', '', $incomes[0][2]);
        $trx['commission'] = preg_replace('/\./', '', $outcomes[0][0]);
        $trx['rental_cost'] = preg_replace('/\./', '', $outcomes[0][1]);
        $trx['adjustment'] = preg_replace('/\./', '', $outcomes[0][2]);
        $trx['workdays'] = count($splitDetail) / 2;

        return [$trx];
    }

    /**
     * Parse pdf file
     */
    public function parsePdf($data)
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
        preg_match('/\d{4}/', $splitSummaries[2], $year);

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

        // Set up data format
        $dataDetailLength = count($splitDetail);
        $result = [];
        
        for ($a = 0; $a < $dataDetailLength; $a += 2) {
            if (!isset($splitDetail[$a + 1])) {
                continue;
            }
            
            $detailContent = $splitDetail[$a + 1];
            $splitContent = explode("\n", $detailContent);
            $splitContent = array_values(array_filter($splitContent, 'strlen'));
            
            // Get amount that maybe occur in header table
            $headerContent = $splitContent[0];
            $amountInHeader = 0;
            if (preg_match('/\d+\.\d+/', $headerContent, $amountContent)) {
                $amountInHeader = $amountContent[0];
            }

            // Handling parsing problems
            $splitContent = $this->handlePageBreaks($splitContent);
            $splitContent = $this->handleMultipleHours($splitContent);

            // Get hours to index 0
            foreach ($splitContent as $k => $value) {
                if (preg_match('/\d+:\d+ [PM|AM]+/', $value)) {
                    \array_splice($splitContent, 0, $k);
                    break;
                }
            }

            // Get detail rows
            $splitCount = count($splitContent);
            $rowCount = 8;
            $contentKey = [
                'time' => 0,
                'type' => 1,
                'amount' => 2,
                'promo' => 3,
                'toll_etc' => 4,
                'total_amount' => 5,
                'commission' => 6,
                'desc' => 7,
            ];

            for ($i = 0; $i < $splitCount; $i += 1) {
                $rows = [];
                $perRow = $rowCount * $i;
                if (
                    !isset($splitContent[$contentKey['time'] + $perRow]) ||
                    !isset($splitContent[$contentKey['type'] + $perRow]) ||
                    !isset($splitContent[$contentKey['amount'] + $perRow]) ||
                    !isset($splitContent[$contentKey['promo'] + $perRow]) ||
                    !isset($splitContent[$contentKey['toll_etc'] + $perRow]) ||
                    !isset($splitContent[$contentKey['total_amount'] + $perRow]) ||
                    !isset($splitContent[$contentKey['commission'] + $perRow]) ||
                    !isset($splitContent[$contentKey['desc'] + $perRow])
                ) {
                    break;
                }

                $amountRow = ($i === 0 && ($amountInHeader > 0)) ? $amountInHeader : $splitContent[$contentKey['amount'] + $perRow];
                $rows = [
                    'date' => $this->convertDate($year[0], $splitDetail[$a], $splitContent[$contentKey['time'] + $perRow]),
                    'time' => $splitContent[$contentKey['time'] + $perRow],
                    'order_code' => '-',
                    'type' => $splitContent[$contentKey['type'] + $perRow],
                    'amount' => $amountRow,
                    'promo' => $splitContent[$contentKey['promo'] + $perRow],
                    'toll_etc' => $splitContent[$contentKey['toll_etc'] + $perRow],
                    'total_amount' => $splitContent[$contentKey['total_amount'] + $perRow],
                    'commission' => $splitContent[$contentKey['commission'] + $perRow],
                    'desc' => $splitContent[$contentKey['desc'] + $perRow],
                ];

                $result[] = $rows;
            }
        }

        return $result;
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
                'GO-BIRDCOMBO Tip' => 1,
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
                'GO-BIRDCOMBO Tip' => 0,
                'GO-BIRDCOMBO' => 0.2
            ]
        ];

        if (!isset($template[$trxType][$type])) {
            return 0;
        }

        return $template[$trxType][$type];
    }

    /**
     * Convert dates from PDF
     */
    public function convertDate ($year, $date, $hour)
    {
        $arrMonths = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        // Date Format
        $splitDate = explode(',', $date);
        $splitDate = explode(' ', ltrim($splitDate[1]));
        $dateFormat = $year . "-" . $arrMonths[$splitDate[1]] . "-" . $splitDate[0] . " " . date("H:i", strtotime($hour));
        
        return $dateFormat;
    }

    /**
     * Handling page breaks from PDF
     */
    public function handlePageBreaks($splitContent)
    {
        $filterPageBreaks = [];
        $foundNewPage = false;
        foreach ($splitContent as $k => $val) {
            if (preg_match('/\d+ of \d+/', $val)) {
                $foundNewPage = true;
                continue;
            }

            if ($foundNewPage && preg_match('/\d+:\d+ [AM|PM]+/', $val)) {
                $filterPageBreaks[] = preg_replace('/[\x00-\x1F\x7F]/', '', $val);
                continue;
            }

            if ($foundNewPage && preg_match('/[ADR|IOS]+.\d+/', $val)) {
                continue;
            }

            $filterPageBreaks[] = $val;
        }

        return $filterPageBreaks;
    }

    /**
     * Handle multiple hours in 1 row
     */
    public function handleMultipleHours ($content, $rowCount = 8)
    {
        $filterMultipleHours = [];
        foreach ($content as $k => $val) {
            preg_match_all('/\d+:\d+ [AM|PM]+/', $val, $matches);
            if (count($matches[0]) > 1) {
                $content[$k] = $matches[0][0];
                foreach ($matches[0] as $i => $hour) {
                    // Only insert hour with index > 0
                    // Because index 0 is already in correct position
                    if ($i > 0) {
                        array_splice($content, $k + ($rowCount * $i), 0, [ $hour ]);
                    }
                }
                
                return $this->handleMultipleHours($content);
            }

            $filterMultipleHours[] = $val;
        }

        return $filterMultipleHours;
    }
}
