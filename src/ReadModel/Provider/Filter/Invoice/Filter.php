<?php


namespace App\ReadModel\Provider\Filter\Invoice;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $providerID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/logInvoice')) {
            $filter = $session->get('filter/logInvoice');
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
        }
    }
}