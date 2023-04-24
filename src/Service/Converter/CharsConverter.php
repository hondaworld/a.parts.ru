<?php


namespace App\Service\Converter;


class CharsConverter
{
    private $eng_chars = ['`', 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '[', ']', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', ';', '\'', 'z', 'x', 'c', 'v', 'b', 'n', 'm', ',', '.', '`', 'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', '[', ']', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ';', '\'', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', ',', '.'];
    private $rus_chars = ['ё', 'й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'Ё', 'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ', 'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э', 'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю'];
    private $lat_chars = ["yo", "y", "c", "u", "k", "e", "n", "g", "sh", "sch", "z", "kh", "y", "f", "y", "v", "a", "p", "r", "o", "l", "d", "zh", "e", "ya", "ch", "s", "m", "i", "t", "", "b", "yu", "yo", "y", "c", "u", "k", "e", "n", "g", "sh", "sch", "z", "kh", "y", "f", "y", "v", "a", "p", "r", "o", "l", "d", "zh", "e", "ya", "ch", "s", "m", "i", "t", "", "b", "yu"];

    public function urlConvert(?string $str): ?string
    {
        if (!$str) return $str;

        $arrAllow = [['-', '_', '_', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['-', ' ', '_', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9']];

        $lat_chars_adv = array_merge($this->lat_chars, $arrAllow[0]);
        $rus_chars_adv = array_merge($this->rus_chars, $arrAllow[1]);

        $val = strtolower(str_replace($rus_chars_adv, $lat_chars_adv, trim($str)));

        $val = preg_replace('#[^a-z0-9\_\-]#i', '', $val);

        return $val;
    }
}