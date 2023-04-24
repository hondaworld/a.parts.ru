<?php


namespace App\Security\Voter\Firm;


use App\Model\Firm\Entity\Firm\Firm;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FirmBalanceVoter extends Voter
{

    public const FIRM_BALANCE = 'firm_balance';
    public const FIRM_BALANCE_CHANGE = 'firm_balance_change';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::FIRM_BALANCE,
                self::FIRM_BALANCE_CHANGE
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('Firm', $attribute);
    }
}