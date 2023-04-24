<?php


namespace App\ReadModel\Card\Filter\ZapCardHistory;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $doc_typeID;
    public $document_num;
    public $dateofadded;
    public $firmID;
    public $zapSkladID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/zapCardHistory')) {
            $filter = $session->get('filter/zapCardHistory');
            if (isset($filter['doc_typeID'])) $this->doc_typeID = $filter['doc_typeID'];
            if (isset($filter['document_num'])) $this->document_num = $filter['document_num'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
            if (isset($filter['firmID'])) $this->firmID = $filter['firmID'];
            if (isset($filter['zapSkladID'])) $this->zapSkladID = $filter['zapSkladID'];
        }
    }
}