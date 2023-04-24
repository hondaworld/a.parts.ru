<?php


namespace App\Security\Voter\Sklad;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SkladVoter extends Voter
{
    public const SKLAD_PARTS = 'sklad_parts';
    public const SKLAD_PART_PRICES = 'sklad_parts_prices';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
            self::SKLAD_PARTS,
            self::SKLAD_PART_PRICES
        ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('ZapSklad', $attribute);
    }
}