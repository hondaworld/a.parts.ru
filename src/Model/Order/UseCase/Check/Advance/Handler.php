<?php

namespace App\Model\Order\UseCase\Check\Advance;


use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;

class Handler
{
    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $kassa = 'msk';
        if ($command->zapSkladID == ZapSklad::SPB) {
            $kassa = 'spb';
        }

        $url = "http://new.parts.ru/pay/payKassaAdvance/?balanceID=" . $command->balanceID . "&managerID=" . $manager->getId() . "&kassa=" . $kassa . "&is_test=" . ($manager->getId() == 1 ? 1 : 0) . "&key=" . md5($manager->getId() . $command->balanceID . $_ENV['KASSA_KEY']);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);

        if ($response != '') {
            $messages[] = [
                'type' => 'info',
                'message' => $response
            ];
        } else {
            $messages[] = [
                'type' => 'success',
                'message' => "Чек распечатан"
            ];
        }

        return $messages;
    }
}
