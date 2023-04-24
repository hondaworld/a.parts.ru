<?php


namespace App\ReadModel\User;


class UserView
{
    public $userID;
    public $name;
    public $firstname;
    public $lastname;
    public $middlename;
    public $organization;
    public $phonemob;

    public function getName(): string
    {
        return $this->lastname . ' ' . $this->firstname . ($this->middlename ? ' ' . $this->middlename : '') . ' - ' . $this->phonemob . ($this->organization ? ' (' . $this->organization . ')' : '');
    }
}