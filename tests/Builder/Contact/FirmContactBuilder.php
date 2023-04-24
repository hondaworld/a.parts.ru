<?php

namespace App\Tests\Builder\Contact;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Firm\Entity\Firm\Firm;

class FirmContactBuilder
{
    private bool $isMain;
    private Town $town;
    private Address $address;
    private Firm $firm;

    public function __construct(Firm $firm, bool $isMain = false)
    {
        $this->isMain = $isMain;
        $this->firm = $firm;
        $this->town = (new TownBuilder())->build();
        $this->address = new Address('123456', 'Плещеева', '8', '1', '50');
    }

    public function build(): Contact
    {
        $contact = new Contact($this->firm, $this->town, $this->address, '+79104651911', '+79104555555', '+7948484848', 'https://www.ru', 'email@domen.ru', 'Описание', false, $this->isMain);
        $this->firm->assignContact($contact);

        return $contact;
    }

}