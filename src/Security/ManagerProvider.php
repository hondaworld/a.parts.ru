<?php


namespace App\Security;


use App\ReadModel\Manager\AuthView;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ManagerProvider implements UserProviderInterface
{
    private $managers;

    public function __construct(ManagerFetcher $managers)
    {
        $this->managers = $managers;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $manager = $this->loadManager($username);
        return $this->identityByManager($manager);
    }

    public function refreshUser(UserInterface $identity): UserInterface
    {
        if (!$identity instanceof ManagerIdentity) {

            throw new UnsupportedUserException('Invalid user class ' . \get_class($identity));
        }

        $manager = $this->loadManager($identity->getUsername());

        return $this->identityByManager($manager);
    }

    public function supportsClass(string $class): bool
    {
        return $class === ManagerIdentity::class;
    }

    private function loadManager(string $username): AuthView
    {
        $manager = $this->managers->findForAuthByLogin($username);

        if (!$manager) {
            throw new UsernameNotFoundException('');
        }

        return $manager;
    }

    private function identityByManager(AuthView $manager): ManagerIdentity
    {
        return new ManagerIdentity(
            $manager->managerID,
            $manager->login,
            $manager->password_admin ?: '',
            $manager->firstname,
            $manager->name,
            $manager->photo,
            $manager->isHide,
            $manager->isManager,
            $manager->isAdmin,
            $manager->settings_admin,
            $manager->sections
        );
    }
}