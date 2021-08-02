<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class YearTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPayDay()
    {
        $randomYears = [
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
            rand(1950,2050),
        ];

        foreach ($randomYears as $randomYear) {
            $response   = $this->call('GET', '/lastDate/'.$randomYear.'/json');

            $decoded    = $response->original;
            
            foreach( $decoded["payDays"] as  $key => $payDay ) {
                $dayTime    = strtotime($payDay);
                $day        = date('w', $dayTime);
                $month      = date('m', $dayTime);
                
                //verify the payment is not in a weekend
                $this->assertFalse( $day === 0 || $day === 6 );

                //if the date is not the last of the month
                if ( $dayTime !== strtotime(date("Y-m-t", $dayTime)) ){
                    //verify if it is on a Friday
                    $this->assertEquals( $day, 5 );
                    //verify it is the last Friday
                    $dayDifference = date("t", $dayTime) - date("d", $dayTime);
                    $this->assertContains($dayDifference, [1, 2]);
                }

                //verify if payday is in the same month
                $this->assertEquals($month, $key + 1);
            }
            foreach( $decoded["bonusDays"] as $key => $bonusDay ) {
                $dayTime    = strtotime($bonusDay);
                $day        = date('w', $dayTime);
                $date       = date('d', $dayTime);
                $month      = date('m', $dayTime);
                $year       = date('Y', $dayTime);
                
                //verify the payment is not in a weekend
                $this->assertFalse( $day === 0 || $day === 6 );

                //if the date is not on the fifteenth
                if ( $date != 15 ) {
                    //verify it is on a Wednesay
                    $this->assertEquals( $day, 3 );
                    //verify it is the next Wednesday after the weekend
                    $dayDifference = date("d", $dayTime) - 15;
                    $this->assertContains($dayDifference, [3, 4]);
                }

                //verify if bonus is payed in the next month
                $checkMonth = $key >= 11 ? $key - 10 : $key + 2;
                $checkYear = $key >= 11 ? $randomYear + 1 : $randomYear; 

                $this->assertEquals($month, $checkMonth);
                $this->assertEquals($year, $checkYear);
            }
            $this->assertEquals( 200, $response->status() );
        }
    }
}
