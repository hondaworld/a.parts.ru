<?php

namespace App\Service\Converter;

class NumberInWordsConverter
{
    public function getWords(string $text, bool $isRub = false): string
    {
        $rub = floatval($text);
        $res = '';

        $dop0 = array(($isRub ? "рублей" : ""), "тысяч", "миллионов", "миллиардов");
        $dop1 = array(($isRub ? "рубль" : ""), "тысяча", "миллион", "миллиард");
        $dop2 = array(($isRub ? "рубля" : ""), "тысячи", "миллиона", "миллиарда");
        $s1 = array("", "один", "два", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять");
        $s11 = array("", "одна", "две", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять");
        $s2 = array("", "десять", "двадцать", "тридцать", "сорок", "пятьдесят", "шестьдесят", "семьдесят", "восемьдесят", "девяносто");
        $s22 = array("десять", "одиннадцать", "двенадцать", "тринадцать", "четырнадцать", "пятнадцать", "шестнадцать", "семнадцать", "восемнадцать", "девятнадцать");
        $s3 = array("", "сто", "двести", "триста", "четыреста");

        if ($rub == 0) {// если это 0
            return "ноль " . $dop0[0];
        }

        // разбиваем полученное число на тройки и загоняем в массив $triplet
        $t_count = ceil(strlen($rub) / 3);
        for ($i = 0; $i < $t_count; $i++) {
            $k = $t_count - $i - 1;
            $triplet[$k] = $rub % 1000;
            $rub = floor($rub / 1000);
        }

        // пробегаем по триплетам
        for ($i = 0; $i < $t_count; $i++) {
            $t = $triplet[$i]; // это текущий триплет - с ним и работаем
            $k = $t_count - $i - 1;
            $n1 = floor($t / 100);
            $n2 = floor(($t - $n1 * 100) / 10);
            $n3 = $t - $n1 * 100 - $n2 * 10;

            // обрабатываем сотни
            if ($n1 < 5) $res .= $s3[$n1] . " ";
            elseif ($n1) $res .= $s1[$n1] . "сот ";

            if ($n2 > 1) {// второй десяток
                $res .= $s2[$n2] . " ";
                if ($n3 and $k == 1) {// если есть единицы в триплете и это триплет ТЫСЯЧ
                    $res .= $s11[$n3] . " ";
                } elseif ($n3) {
                    $res .= $s1[$n3] . " ";
                }
            } elseif ($n2 == 1) {
                $res .= $s22[$n3] . " ";
            } elseif ($n3 and $k == 1) {// если есть единицы в триплете и это триплет ТЫСЯЧ
                $res .= $s11[$n3] . " ";
            } elseif ($n3) {
                $res .= $s1[$n3] . " ";
            }

            // прилепляем в конец триплета коммент
            if ($n3 == 1 and $n2 != 1) {// в конце триплета стоит 1, но не 11.
                $res .= $dop1[$k] . " ";
            } elseif ($n3 > 1 and $n3 < 5 and $n2 != 1) {// в конце триплета стоит 2, 3 или 4, но не 12, 13 или 14
                $res .= $dop2[$k] . " ";
            } elseif ($t or $k == 0) {
                $res .= $dop0[$k] . " ";
            }
        }
        return $res;
    }

    public function getKop(string $text): string
    {
        $str = floatval($text);

        if (strpos($str, ".") !== false) {
            $str1 = substr($str, strpos($str, ".") + 1, 2);
            if (strlen($str1) == 1) $str1 .= "0";
        } else
            $str1 = "00";

        $d = substr($str1, 1) * 1;
        if ($d == 1) $str1 .= " копейка";
        else if (($d == 2) || ($d == 3) || ($d == 4)) $str1 .= " копейки";
        else $str1 .= " копеек";

        return $str1;
    }
}