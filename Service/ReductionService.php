<?php

namespace Service;

use Entity\Promocode;
use Entity\RedeemInfo;

class ReductionService
{
    public function reductionAskAnswer(RedeemInfo $redeemInfo, Promocode $promocode): array
    {
        $errorMain = [];
        foreach ($promocode->getRestrictions() as $key => $restriction){
            $result = $this->testRestriction($key, $redeemInfo, $restriction);

            if($result != []){
                $errorMain[$key] = $result;
            }
        }

        if($errorMain != []) {
          return [
              'promocode_name' => $promocode->getName(),
              'status' => 'denied',
              'reasons' => $errorMain
          ];
        }

        return [
            'avantage' => [
                'percent' => $promocode->getAvantage()['percent']
            ],
            'promocode_name' => $promocode->getName(),
            'status' => 'accepted'
        ];
    }

    private function checkNumberRestrictions(mixed $value, mixed $restriction): array
    {
        $error = [];
        foreach ($restriction as $key => $condition) {
            switch ($key){
                case 'gt':
                    if(!($value > $condition)){
                        $error['gt'] = 'IsNotGt';
                    }
                    break;

                case 'lt':
                    if(!($value < $condition)){
                        $error['lt'] = 'IsNotLt';
                    }
                    break;

                case 'eq':
                    if(!($value == $condition)){
                        $error['eq'] = 'IsNotEq';
                    }
                    break;
            }
        }

        return $error;
    }

    private function testRestriction(int|string $key, RedeemInfo $redeemInfo, mixed $restriction): array
    {
        $error = [];
        switch ($key) {
            case '@age':
                if (!isset($redeemInfo->getArguments()['age'])) {
                    $error = 'Age value is missing';
                } else {
                    $checkNumberRestrictions = $this->checkNumberRestrictions($redeemInfo->getArguments()['age'], $restriction);
                    if ($checkNumberRestrictions != []) {
                        $error = $checkNumberRestrictions;
                    }
                }

                break;

            case '@date':
                if (!isset($redeemInfo->getArguments()['date'])) {
                    $error[$key] = 'Date value is missing';
                } else {
                    $checkDateRestrictions = $this->checkDateRestrictions($redeemInfo->getArguments()['date'], $restriction);
                    if ($checkDateRestrictions != []) {
                        $error[$key] = $checkDateRestrictions;
                    }
                }

                break;

            case '@meteo':
                break;

            case '@or':
                $error = [];
                $subRestrictionCount = count($restriction);

                foreach ($restriction as $key => $subRestriction){
                    $result = $this->testRestriction(array_keys($subRestriction)[0], $redeemInfo, array_values($subRestriction)[0]);

                    if($result != []){
                        $error[$key] = $result;
                    }
                }

                // dans le cas d'un OU, s'il y a une restriction qui est OK, on retourne aucune erreur
                if(count($error) < $subRestrictionCount){
                    $error = [];
                }
                break;
        }

        // si $error n'est pas vide, c'est qu'il y a eu une erreur dans l'un des cas
        // false et true et true => false
        return $error;
    }

    private function checkDateRestrictions(mixed $date, mixed $restriction): array
    {
        $error = [];
        foreach ($restriction as $key => $condition) {
            switch ($key){
                case 'before':
                    if(!(strtotime($date) < strtotime($condition))){
                        $error['before'] = 'IsNotBefore';
                    }
                    break;

                case 'after':
                    if(!(strtotime($date) > strtotime($condition))){
                        $error['after'] = 'IsNotAfter';
                    }
                    break;
            }
        }

        return $error;
    }
}
