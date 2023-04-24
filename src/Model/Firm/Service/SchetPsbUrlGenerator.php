<?php

namespace App\Model\Firm\Service;

use App\Model\Firm\Entity\Schet\Schet;
use App\Service\Converter\CharsConverter;

class SchetPsbUrlGenerator
{
    private $url = 'https://3ds.payment.ru/cgi-bin/payment_ref/generate_payment_ref';
    private $url_test = 'https://test.3ds.payment.ru/cgi-bin/payment_ref/generate_payment_ref';
    private $host = '3ds.payment.ru';
    private $host_test = 'test.3ds.payment.ru';
    private $termianl = '29581601';

    private CharsConverter $charsConverter;

    public function __construct(CharsConverter $charsConverter)
    {
        $this->charsConverter = $charsConverter;
    }

    public function getUrl(Schet $schet): array
    {
        $comp1 = 'C50E41160302E0F5D6D59F1AA3925C45';
        $comp2 = '00000000000000000000000000000000';
        $data = [
            'amount' => number_format($schet->getSumm(), 2, '.', ''),
            'currency' => 'RUB',
            'order' => $schet->getDocument()->getSchetNum(),
            'desc' => strtoupper($this->charsConverter->urlConvert($schet->getUser()->getUserName()->getFullname())),
            'terminal' => $this->termianl,
            'trtype' => '1',
//            'email' => 'info@hondaworld.ru',
//            'backref' => '',
        ];
        dump($data);
        $vars = ["amount", "currency", "terminal", "trtype", "backref", "order"];
        $string = '';
        foreach ($vars as $param) {
            if (isset($data[$param]) && strlen($data[$param]) != 0) {
                $string .= strlen($data[$param]) . $data[$param];
            } else {
                $string .= "-";
            }
        }
        dump($string);
        $key = strtoupper(implode(unpack("H32", pack("H32", $comp1) ^ pack("H32", $comp2))));
        $data['p_sign'] = strtoupper(hash_hmac('sha256', $string, pack('H*', $key)));
        $url = $this->url_test;
        $host = $this->host_test;
        $headers = [
            "Host: " . $host,
            "User-Agent: " . $_SERVER['HTTP_USER_AGENT'],
            "Accept: */*",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ];
        $params = array_change_key_case($data, CASE_UPPER);
        $query = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (!$response) {
//            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($response, true);

    }
}