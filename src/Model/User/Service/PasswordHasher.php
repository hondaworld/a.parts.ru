<?php


namespace App\Model\User\Service;

class PasswordHasher
{
    public function hash(string $password): string
    {
        $hash = md5($password);
        if ($hash === false) {
            throw new \RuntimeException('Unable to generate hash.');
        }
        return $hash;
    }

    public function validate(string $password, string $hash): bool
    {
        return $this->hash($password) == $hash;
    }
}
