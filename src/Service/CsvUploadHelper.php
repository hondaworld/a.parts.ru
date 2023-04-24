<?php


namespace App\Service;


class CsvUploadHelper
{
    public function getCsvLine($data, string $razd = ';')
    {
        return fgetcsv($data, 4096, $razd, '"', '"');
    }

    public function convertText(string $str): string
    {
        return !$this->is_utf($str) ? $this->iconv_text($str) : $str;
    }

    public function convertTextToCP1251(string $str): string
    {
        return $this->iconv_text_to_cp1251($str);
    }

    private function iconv_text(string $str): string
    {
        return mb_convert_encoding($str, "UTF-8", "Windows-1251");
    }

    private function iconv_text_to_cp1251(string $str): string
    {
        return mb_convert_encoding($str, "Windows-1251", "UTF-8");
    }

    private function is_utf(string $str): string
    {
        return mb_convert_encoding($str, "UTF-8", "UTF-8") == $str;
    }
}