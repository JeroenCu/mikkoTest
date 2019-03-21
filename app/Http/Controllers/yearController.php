<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use DateTime;

class YearController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {}

    public function getPayDays($year, $outputType = 'csv') {
        $yearMonths = [];
        $payDays    = [];
        $bonusDays  = [];
        for ($i = 1; $i <= 12; $i++) {
            $lastDay = date("Y/m/t", strtotime($year."-".$i."-1"));
            array_push($payDays, $lastDay);
            array_push($bonusDays, $year."/".$i."/15");
        }
        foreach($payDays as $key => $day){
            $dayOfWeek = date('w', strtotime($day));
            switch($dayOfWeek) {
                case 6:
                    $payDays[$key] = date('Y/m/d',(strtotime ( '-1 day' , strtotime ( $day))));
                    break;
                case 0:
                    $payDays[$key] = date('Y/m/d',(strtotime ( '-2 days' , strtotime ( $day))));
                    break;
            }
        }
        foreach($bonusDays as $key => $day){
            $dayOfWeek = date('w', strtotime($day));
            switch($dayOfWeek) {
                case 6:
                    $bonusDays[$key] = date('Y/m/d',(strtotime ( '+4 days' , strtotime ( $day))));
                    break;
                case 0:
                    $bonusDays[$key] = date('Y/m/d',(strtotime ( '+3 days' , strtotime ( $day))));
                    break;
            }
        }

        if ($outputType === 'json') {
            return response()->json(['year' => $year, 'payDays' => $payDays, 'bonusDays' => $bonusDays]);
        }

        $headers = $this->getFileResponseHeaders($year.'.csv');
        $columns = ["month", "Payday", "Bonus Payday"];

        return $this->streamFile(function () use ($payDays, $bonusDays, $columns) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $columns);

            foreach ($payDays as $key => $payday) {
                $dateObj   = DateTime::createFromFormat('!m', $key + 1);
                $monthName = $dateObj->format('F');
                fputcsv($output, [
                    $monthName, 
                    date('d/m/Y', strtotime($payday)), 
                    date('d/m/Y', strtotime($bonusDays[$key]))
                    ]);
            }
            fclose($output);
        }, $headers);
    }

    protected function streamFile($callback, $headers)
    {   
        try {
            $response = new StreamedResponse($callback, 200, $headers);
            $response->send();
        }
        catch(Exception $e) {
            var_dump($e);
        }
    }

    protected function getFileResponseHeaders($filename)
    {
        return [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
        ];
    }
}
