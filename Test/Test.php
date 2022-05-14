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
            '@age' => 15
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
}
