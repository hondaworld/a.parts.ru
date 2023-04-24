<?php


namespace App\Security\Voter\Provider;


use App\Model\Provider\Entity\Provider\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProviderPriceVoter extends Voter
{

    public const PROVIDER_PRICE_TRUNCATE = 'provider_price_truncate';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::PROVIDER_PRICE_TRUNCATE
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

        return $manager->getActionByEntity('ProviderPrice', $attribute);
    }
}