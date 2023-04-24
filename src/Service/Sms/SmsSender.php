<?php

namespace App\Service\Sms;


use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use stdClass;

class SmsSender
{
    private User $user;
    private Manager $manager;

    public function send(Manager $manager, User $user, $text, $from)
    {
        $this->user = $user;
        $this->manager = $manager;

        $smsru = new SmsRu();
        $data = new stdClass();
        $data->to = $this->user->getPhonemob();
        $data->text = $text; // Текст сообщения
        $data->from = $from;
//        $data->test = 1;
        $sms = $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную

        $this->save($sms->status_code, $sms->status_text ?? '', $sms->sms_id ?? '', $from, $text);
    }

    public function sendFromParts(Manager $manager, User $user, $text)
    {
        $this->send($manager, $user, $text, 'Parts.Ru');
    }

    private function save($status_code, $status_text, $sms_id, $sender, $text)
    {
        $this->user->assignUserSms($this->manager, $status_code, $status_text, $sms_id, $sender, $text);
    }
}