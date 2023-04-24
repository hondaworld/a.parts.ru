<?php


namespace App\Security\Voter\Sklad;


use App\Model\Sklad\Entity\PriceList\PriceList;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PriceListVoter extends Voter
{

    public const PRICE_LIST_OPT_CHANGE = 'opt_change';
    public const GROUP_CHANGE = 'group_change';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::PRICE_LIST_OPT_CHANGE,
                self::GROUP_CHANGE
            ], true) && $subject instanceof PriceList;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof PriceList) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('PriceList', $attribute);
    }
}