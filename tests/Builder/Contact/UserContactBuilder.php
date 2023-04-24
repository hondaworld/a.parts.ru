<?php

namespace App\Tests\Builder\Contact;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Town\Town;
use App\Model\User\Entity\User\User;

class UserContactBuilder
{
    private bool $isMain;
    private Town $town;
    private Address $address;
    private User $user;

    public function __construct(User $user, bool $isMain = false)
    {
        $this->isMain = $isMain;
        $this->user = $user;
        $this->town = (new TownBuilder())->build();
        $this->address = new Address('123456', 'Плещеева', '8', '1', '50');
    }

    public function build(): Contact
    {
        $contact = new Contact($this->user, $this->town, $this->address, '+79104651911', '+79104555555', '+7948484848', 'https://www.ru', 'email@domen.ru', 'Описание', false, $this->isMain);
        $this->user->assignContact($contact);

        return $contact;
    }
}