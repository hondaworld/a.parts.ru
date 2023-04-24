<?php


namespace App\ReadModel\Firm\Filter\AllDocuments;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $from_name;
    public $to_name;
    public $dateofadded;
    public $document_num;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/allDocuments')) {
            $filter = $session->get('filter/allDocuments');
            if (isset($filter['from_name'])) $this->from_name = $filter['from_name'];
            if (isset($filter['to_name'])) $this->to_name = $filter['to_name'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
            if (isset($filter['document_num'])) $this->document_num = $filter['document_num'];
        }
    }
}