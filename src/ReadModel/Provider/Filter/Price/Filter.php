<?php


namespace App\ReadModel\Provider\Filter\Price;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $name;
    public $description;
    public $showHide;
    public $providerID;
    public $price;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/providerPrices')) {
            $filter = $session->get('filter/providerPrices');
            if (isset($filter['showHide'])) $this->showHide = $filter['showHide'];
            if (isset($filter['name'])) $this->name = $filter['name'];
            if (isset($filter['description'])) $this->description = $filter['description'];
            if (isset($filter['providerID'])) $this->providerID = $filter['providerID'];
            if (isset($filter['price'])) $this->price = $filter['price'];
        }
    }
}