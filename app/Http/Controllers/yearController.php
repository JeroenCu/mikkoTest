<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use DateTime;

class YearController extends Controller
{
    public function getPayDays($year, $outputType = 'csv') {
        $yearMonths = [];
        $payDays    = [];
        $bonusDays  = [];

        for ($i = 1; $i <= 12; $i++) {
            $lastDay = date("Y/m/t", strtotime($year . "-" . $i . "-1"));
            array_push($payDays, $lastDay);
            if ($i === 12) {
                $nextYear = $year + 1;
                array_push($bonusDays, $nextYear . "/1/15");
            }
            else {
                $nextMonth = $i + 1;
                array_push($bonusDays, $year . "/" . $nextMonth . "/15");
            }
        }

        $this->processPaydays($payDays);
        $this->processBonusdays($bonusDays);

        if ($outputType === 'json') {
            return response()->json(['year' => $year, 'payDays' => $payDays, 'bonusDays' => $bonusDays]);
        }

        $filename = $year.'.csv';
        $headers = $this->getFileResponseHeaders($filename);
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

    private function streamFile($callback, $headers)
    {   
        try {
            $response = new StreamedResponse($callback, 200, $headers);
            $response->send();
        }
        catch(Exception $e) {
            var_dump($e);
        }
    }

    private function getFileResponseHeaders($filename)
    {
        return [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
        ];
    }

    private function processPayDays(&$payDays): void
    {
        //if the payday is a weekend day, we take the previous Friday (PHP has Sunday as day 0)
        $cases = [
            0 => '-2 days',
            6 => '-1 day'
        ];
        $this->processDays($payDays, $cases);
    }

    private function processBonusDays(&$bonusDays): void
    {
        //if the 15th is a weekend day, we take the next Wednesday (PHP has Sunday as day 0)
        $cases = [
            0 => '+3 days',
            6 => '+4 days'
        ];
        $this->processDays($bonusDays, $cases);
    }

    private function processDays(&$days, $cases): void
    {
        foreach($days as $key => $day){
            $dayOfWeek = date('w', strtotime($day));
            switch($dayOfWeek) {
                case 6:
                    $days[$key] = date('Y/m/d',(strtotime ( $cases[6] , strtotime ( $day))));
                    break;
                case 0:
                    $days[$key] = date('Y/m/d',(strtotime ( $cases[0] , strtotime ( $day))));
                    break;
            }
        }
    }
}
