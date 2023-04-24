<?php


namespace App\Security\Voter\User;


use App\Model\User\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{

    public const USER_OPT_CHANGE = 'opt_change';
    public const USER_CONTACTS = 'contacts';
    public const USER_CONTACTS_CHANGE = 'contacts_change';
    public const USER_BEZNALS = 'beznals';
    public const USER_BEZNALS_CHANGE = 'beznals_change';
    public const USER_DOCUMENTS = 'documents';
    public const USER_DOCUMENTS_CHANGE = 'documents_change';
    public const USER_DOP = 'dop';
    public const USER_SETTINGS = 'settings';
    public const USER_BALANCE = 'balance';
    public const USER_BALANCE_CHANGE = 'balance_change';
    public const USER_BALANCE_CHANGE_FINANCE_TYPE = 'balance_change_finance_type';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::USER_OPT_CHANGE,
                self::USER_CONTACTS,
                self::USER_CONTACTS_CHANGE,
                self::USER_BEZNALS,
                self::USER_BEZNALS_CHANGE,
                self::USER_DOCUMENTS,
                self::USER_DOCUMENTS_CHANGE,
                self::USER_DOP,
                self::USER_SETTINGS,
                self::USER_BALANCE,
                self::USER_BALANCE_CHANGE,
                self::USER_BALANCE_CHANGE_FINANCE_TYPE
            ], true) && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('User', $attribute);
    }
}