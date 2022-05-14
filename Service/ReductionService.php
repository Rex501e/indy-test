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
                if (!isset($redeemInfo->getArguments()['meteo']['town'])) {
                    $error[$key] = 'Town value is missing';
                } else {
                    $checkMeteoRestrictions = $this->checkMeteoRestrictions($redeemInfo->getArguments()['meteo']['town'], $restriction);
                    if ($checkMeteoRestrictions != []) {
                        $error = $checkMeteoRestrictions;
                    }
                }
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

    private function checkMeteoRestrictions(mixed $town, mixed $restriction)
    {
        $error = [];
        // récupération de la météo actuelle de la ville
        $urlCoordonatesTown = 'http://api.openweathermap.org/geo/1.0/direct?q=' . $town . '&limit=1&appid=d0562f476913da692a065c608d0539f6';

        $resp = $this->curlRequest($urlCoordonatesTown);

        $longitude = doubleval($resp[0]['lon']);
        $latitude = doubleval($resp[0]['lat']);

        $urlWeatherTown = 'https://api.openweathermap.org/data/2.5/weather?lat=' . $latitude . '&lon=' . $longitude . '&appid=d0562f476913da692a065c608d0539f6';

        $resp = $this->curlRequest($urlWeatherTown);

        $weatherIs = strtolower($resp['weather'][0]['main']);

        // conversion Kelvin -> Celsius
        $temperatureCelsius = $resp['main']['temp'] - 273.15;

        if (isset($restriction['is']) && $restriction['is'] != $weatherIs) {
            $error['is'] = 'isNot'.$restriction['is'];
        }

        if (isset($restriction['temp'])){
            $checkNumberRestrictions = $this->checkNumberRestrictions($temperatureCelsius, $restriction['temp']);
            if ($checkNumberRestrictions != []) {
                $error['temp'] = $checkNumberRestrictions;
            }
        }

        return $error;
    }

    private function curlRequest(string $urlCoordonatesTown): mixed
    {
        $curl = curl_init($urlCoordonatesTown);
        curl_setopt($curl, CURLOPT_URL, $urlCoordonatesTown);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // option rajouter car sinon il y avait une erreur de certificat puisque j'interroge depuis du HTTP -> HTTPS
        // En production, il faudrait générer un certificat pour le HTTPS
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        return json_decode($resp, true);
    }


}
