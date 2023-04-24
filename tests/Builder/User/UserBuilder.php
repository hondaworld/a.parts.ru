<?php

namespace App\Tests\Builder\User;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Ur;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Phonemob;

class UserBuilder
{
    private Opt $opt;
    private Name $name;
    private ?Ur $ur = null;
    private string $phonemob = '';

    public function __construct(string $phonemob = '+7 910 465 1911')
    {
        $this->opt = new Opt('Test', 1);
        $this->name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );
        $this->phonemob = $phonemob;
    }

    public function withUr(?Ur $ur = null): self
    {
        $clone = clone $this;
        if ($ur) {
            $clone->ur = $ur;
        } else {
            $clone->ur = new Ur(
                'ООО "Запчасти"',
                '1234567',
                '77000011',
                '1548480',
                '154894894894456',
                true,
                true,
                '1',
                new \DateTime()
            );
        }
        return $clone;
    }

    public function build(): User
    {
        $user = new User(
            $this->opt,
            (new Phonemob($this->phonemob))->getValue(),
            $this->name,
            $this->name->generateName(),
            null,
        );

        if ($this->ur) {
            $user->updateUr($user->getName(), $this->ur);
        }

        return $user;
    }
}