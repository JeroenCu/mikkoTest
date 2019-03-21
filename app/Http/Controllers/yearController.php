<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

class YearController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {}

    public function getPayDays($year) {
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
        $headers = $this->getFileResponseHeaders($year.'.csv');
        $columns = ["month", "Payday", "Bonus Payday"];

        return $this->streamFile(function () use ($payDays, $bonusDays, $columns) {
            $csv = fopen('php://output', 'w');
            fputcsv($csv, $columns);

            foreach ($payDays as $key => $payday) {
                $dateObj   = DateTime::createFromFormat('!m', $key + 1);
                $monthName = $dateObj->format('F');
                fputcsv($csv, [$monthName, $payday, $bonusDays[$key]]);
            }
            fclose($output);
        }, $headers);
    }

    protected function streamFile($callback, $headers)
    {
        $response = new StreamedResponse($callback, 200, $headers);
        $response->send();
    }

    protected function getFileResponseHeaders($filename)
    {
        return [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$filename,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
    }
}
