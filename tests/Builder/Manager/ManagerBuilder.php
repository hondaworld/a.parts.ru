<?php

namespace App\Tests\Builder\Manager;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\Name;
use App\Model\Manager\Entity\Type\ManagerType;

class ManagerBuilder
{
    private ManagerType $type;
    private Name $name;
    private string $login;

    public function __construct(string $login = 'login')
    {
        $this->type = new ManagerType();
        $this->name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );
        $this->login = $login;
    }

    public function build(): Manager
    {
        $manager = new Manager(
            $this->login,
            'password',
            $this->name,
            $this->name->generateName(),
            $this->type
        );

        return $manager;
    }
}