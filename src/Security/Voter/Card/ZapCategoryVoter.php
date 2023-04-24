<?php


namespace App\Security\Voter\Card;


use App\Model\Card\Entity\Category\ZapCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ZapCategoryVoter extends Voter
{

    public const ZAP_GROUP_CHANGE = 'zap_group_change';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::ZAP_GROUP_CHANGE
            ], true) && $subject instanceof ZapCategory;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof ZapCategory) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('ZapCategory', $attribute);
    }
}