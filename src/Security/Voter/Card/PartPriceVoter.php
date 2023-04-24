<?php


namespace App\Security\Voter\Card;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PartPriceVoter extends Voter
{

    public const PART_PRICE_SHOW_PROVIDER_PRICE_DESCRIPTION = 'part_price_show_provider_price_description';
    public const PART_PRICE_SHOW_PRICE_ZAK = 'part_price_show_price_zak';
    public const PART_PRICE_SHOW_DATE_PRICE = 'part_price_show_date_price';
    public const PART_PRICE_CHANGE_OPT = 'part_price_change_opt';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::PART_PRICE_SHOW_PROVIDER_PRICE_DESCRIPTION,
                self::PART_PRICE_SHOW_PRICE_ZAK,
                self::PART_PRICE_SHOW_DATE_PRICE,
                self::PART_PRICE_CHANGE_OPT
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('PartPrice', $attribute);
    }
}