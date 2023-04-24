<?php


namespace App\ReadModel\Ticket\Filter\ClientTicket;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $groupID;
    public $text;
    public $ticket_num;
    public $managerClosed;
    public $answered;
    public $dateofanswer;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/clientTicket')) {
            $filter = $session->get('filter/clientTicket');
            if (isset($filter['groupID'])) $this->groupID = $filter['groupID'];
            if (isset($filter['ticket_num'])) $this->ticket_num = $filter['ticket_num'];
            if (isset($filter['managerClosed'])) $this->managerClosed = $filter['managerClosed'];
            if (isset($filter['answered'])) $this->answered = $filter['answered'];
            if (isset($filter['text'])) $this->text = $filter['text'];
            if (isset($filter['dateofanswer'])) $this->dateofanswer = $filter['dateofanswer'];
        }
    }
}