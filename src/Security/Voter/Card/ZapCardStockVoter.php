<?php


namespace App\Security\Voter\Card;


use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ZapCardStockVoter extends Voter
{

    public const ZAP_CARD_STOCK_NUMBERS = 'zap_card_stock_numbers';
    public const ZAP_CARD_STOCK_NUMBERS_CHANGE = 'zap_card_stock_numbers_change';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::ZAP_CARD_STOCK_NUMBERS,
                self::ZAP_CARD_STOCK_NUMBERS_CHANGE
            ], true) && $subject instanceof ZapCardStock;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof ZapCardStock) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('ZapCardStock', $attribute);
    }
}