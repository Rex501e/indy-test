<?php

include '../Service/ReductionService.php';
include '../Entity/Promocode.php';
include '../Entity/RedeemInfo.php';

use Entity\Promocode;
use Entity\RedeemInfo;
use PHPUnit\Framework\TestCase;
use Service\ReductionService;

class Test extends TestCase
{
    public function testSimpleReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeSimple");
        $promoCode->setAvantage(['percent' =>  25]);
        $promoCode->setRestrictions([
            '@age' => [
                'gt' => 10,
                'lt' => 20,
            ],
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAge');
        $redeemInfo->setArguments([
            'age' => 15
        ]);

        $this->assertSame(
            [
                'avantage' => [
                    'percent' => 25
                ],
                'promocode_name' => 'WeatherCodeAgeSimple',
                'status' => 'accepted'
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }

    public function testSimple2ReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeSimple");
        $promoCode->setAvantage(['percent' =>  25]);
        $promoCode->setRestrictions([
            '@age' => [
                'gt' => 10,
                'lt' => 20,
            ],
            '@date' => [
                'after' => '2021-01-01',
                'before' => '2022-01-01'
            ],
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAge');
        $redeemInfo->setArguments([
            'age' => 9,
            'date' => '2020-03-02'
        ]);

        $this->assertSame(
            [
                'avantage' => [
                    'percent' => 25
                ],
                'promocode_name' => 'WeatherCodeAgeSimple',
                'status' => 'accepted'
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }

    public function testComplexReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeComplex");
        $promoCode->setAvantage(['percent' =>  20]);
        $promoCode->setRestrictions([
            '@or' => [
                0 => [
                    '@age' => [
                        'eq' => 40,
                    ],
                ],
                1 => [
                    '@age' => [
                        'gt' => 15,
                        'lt' => 30,
                    ],
                ],
            ],
            '@date' => [
                'after' => '2021-01-01',
                'before' => '2022-01-01'
            ],
            '@meteo' => [
                'is' => 'clear',
                'temp' => [
                    'lt' => 100
                ],
            ],
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAgeComplex');
        $redeemInfo->setArguments([
            'age' => 16,
            'meteo' => [
                'town' => 'Lyon'
            ],
        ]);

        $this->assertSame(
            [
                'avantage' => [
                    'percent' => 20
                ],
                'promocode_name' => 'WeatherCodeAgeComplex',
                'status' => 'accepted'
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }
}
