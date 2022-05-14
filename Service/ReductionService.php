<?php

namespace Service;

use Entity\Promocode;
use Entity\RedeemInfo;

class ReductionService
{
    public function reductionAskAnswer(RedeemInfo $redeemInfo, Promocode $promocode)
    {
        $isOK = true;

        foreach ($promocode->getRestrictions() as $key => $restriction){
            switch ($key){

                case '@age':
                    if ($redeemInfo->getArguments()['age'] == null) {
                        break;
                    } else {
                        $isOK = $this->checkAgeRestrictions($redeemInfo->getArguments()['age'], $restriction);
                    }
                    break;
            }

            if ($isOK !== true) {
               return $isOK;
            }
        }

        return [
            'avantage' => [
                'percent' => $promocode->getAvantage()['percent']
            ],
            'promocode_name' => $promocode->getName(),
            'status' => 'accepted'
        ];
    }

    private function checkAgeRestrictions(mixed $age, mixed $restriction)
    {
        foreach ($restriction as $key => $condition) {
            switch ($key){
                case 'gt':
                    return $age > $condition;

                case 'lt':
                    return $age < $condition;
            }
        }
    }
}
