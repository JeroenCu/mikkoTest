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
        $randomYear = rand(1950,2050);
        $response   = $this->call('GET', '/lastDate/'.$randomYear.'/json');

        $decoded    = $response->original;
        
        foreach( $decoded["payDays"] as $payDay ) {
            $dayTime    = strtotime($payDay);
            $day        = date('w', $dayTime);

            $this->assertFalse( $day === 0 || $day === 6 );
            if ( $dayTime !== strtotime(date("Y-m-t", $dayTime)) ){
                $this->assertEquals( $day, 5 );
            }
        }
        foreach( $decoded["bonusDays"] as $bonusDay ) {
            $dayTime    = strtotime($bonusDay);
            $day        = date('w', $dayTime);
            $date       = date('d', $dayTime);
            
            $this->assertFalse( $day === 0 || $day === 6 );
            if ( $date != 15 ) {
                $this->assertEquals( $day, 3 );
            }
        }
        $this->assertEquals( 200, $response->status() );
    }
}
