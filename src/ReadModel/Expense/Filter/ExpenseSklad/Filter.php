<?php


namespace App\ReadModel\Expense\Filter\ExpenseSklad;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $document_num;
    public $createrID;
    public $number;
    public $zapSkladID;
    public $zapSkladID_to;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/expenseSklads')) {
            $filter = $session->get('filter/expenseSklads');
            if (isset($filter['document_num'])) $this->document_num = $filter['document_num'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
            if (isset($filter['zapSkladID_to'])) $this->zapSkladID_to = $filter['zapSkladID_to'];
        }
    }
}