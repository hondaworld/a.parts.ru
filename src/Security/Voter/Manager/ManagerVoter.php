<?php


namespace App\Security\Voter\Manager;


use App\Model\Manager\Entity\Manager\Manager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ManagerVoter extends Voter
{

    public const MANAGER_CONTACTS = 'contacts';
    public const MANAGER_CONTACTS_CHANGE = 'contacts_change';
    public const MANAGER_BEZNALS = 'beznals';
    public const MANAGER_BEZNALS_CHANGE = 'beznals_change';
    public const MANAGER_DOCUMENTS = 'documents';
    public const MANAGER_DOCUMENTS_CHANGE = 'documents_change';
    public const MANAGER_FIRMS = 'firms';
    public const MANAGER_AUTH = 'auth';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::MANAGER_CONTACTS,
                self::MANAGER_CONTACTS_CHANGE,
                self::MANAGER_BEZNALS,
                self::MANAGER_BEZNALS_CHANGE,
                self::MANAGER_DOCUMENTS,
                self::MANAGER_DOCUMENTS_CHANGE,
                self::MANAGER_FIRMS,
                self::MANAGER_AUTH
            ], true) && $subject instanceof Manager;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Manager) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('Manager', $attribute);
    }
}