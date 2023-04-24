<?php


namespace App\ReadModel\Expense\Filter\SchetFakKor;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $user_name;
    public $firmID;
    public $dateofadded;
    public $document_num;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/schetFakKor')) {
            $filter = $session->get('filter/schetFakKor');
            if (isset($filter['firmID'])) $this->firmID = $filter['firmID'];
            if (isset($filter['user_name'])) $this->user_name = $filter['user_name'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
            if (isset($filter['document_num'])) $this->document_num = $filter['document_num'];
        }
    }
}