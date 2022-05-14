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
        $promoCode->setAvantage(['percent' => 25]);
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
        $promoCode->setAvantage(['percent' => 25]);
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
            'age' => 11,
            'date' => '2021-03-02'
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

    public function testSimpleErrorReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeSimple");
        $promoCode->setAvantage(['percent' => 25]);
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
            'date' => '2021-03-02'
        ]);

        $this->assertSame(
            [
                'promocode_name' => 'WeatherCodeAgeSimple',
                'status' => 'denied',
                'reasons' => [
                    '@age' => [
                        'gt' => 'IsNotGt'
                    ],
                ],
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }

    public function testComplexReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeComplex");
        $promoCode->setAvantage(['percent' => 20]);
        $promoCode->setRestrictions([
            '@or' => [
                [
                    '@age' => [
                        'eq' => 40,
                    ],
                ],
                [
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
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAgeComplex');
        $redeemInfo->setArguments([
            'age' => 16,
        ]);

        $this->assertSame(
            [
                'promocode_name' => 'WeatherCodeAgeComplex',
                'status' => 'denied',
                'reasons' => [
                    '@date' => [
                        '@date' => 'Date value is missing',
                    ],
                ],
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }

    public function testComplexErrorReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeComplex");
        $promoCode->setAvantage(['percent' => 20]);
        $promoCode->setRestrictions([
            '@or' => [
                [
                    '@age' => [
                        'eq' => 40,
                    ],
                ],
                [
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
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAgeComplex');
        $redeemInfo->setArguments([
            'age' => 1,
        ]);

        $this->assertSame(
            [
                'promocode_name' => 'WeatherCodeAgeComplex',
                'status' => 'denied',
                'reasons' => [
                    '@or' => [
                        0 => [
                            'eq' => 'IsNotEq',
                        ],
                        1 => [
                            'gt' => 'IsNotGt',
                        ],
                    ],
                    '@date' => [
                        '@date' => 'Date value is missing',
                    ],
                ],
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }

    public function testComplexMeteoErrorReductionAskAnswer()
    {
        $reductionService = new ReductionService();

        $promoCode = new Promocode();
        $promoCode->setName("WeatherCodeAgeComplex");
        $promoCode->setAvantage(['percent' => 20]);
        $promoCode->setRestrictions([
            '@or' => [
                [
                    '@age' => [
                        'eq' => 40,
                    ],
                ],
                [
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
//            '@meteo' => [
//                'is' => 'clear',
//                'temp' => [
//                    'lt' => 100
//                ],
//            ],
        ]);

        $redeemInfo = new RedeemInfo();
        $redeemInfo->setPromocodeName('WeatherCodeAgeComplex');
        $redeemInfo->setArguments([
            'age' => 1,
        ]);

        $this->assertSame(
            [
                'promocode_name' => 'WeatherCodeAgeComplex',
                'status' => 'denied',
                'reasons' => [
                    '@or' => [
                        0 => [
                            'eq' => 'IsNotEq',
                        ],
                        1 => [
                            'gt' => 'IsNotGt',
                        ],
                    ],
                    '@date' => [
                        '@date' => 'Date value is missing',
                    ],
                ],
            ],
            $reductionService->reductionAskAnswer($redeemInfo, $promoCode)
        );
    }
}
