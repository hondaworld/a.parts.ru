<?php


namespace App\Security\Voter\Work;


use App\Model\Work\Entity\Category\WorkCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class WorkCategoryVoter extends Voter
{

    public const WORK_GROUP_CHANGE = 'work_group_change';
    public const WORK_GROUP_DELETE = 'work_group_delete';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::WORK_GROUP_CHANGE,
                self::WORK_GROUP_DELETE
            ], true) && $subject instanceof WorkCategory;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof WorkCategory) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('WorkCategory', $attribute);
    }
}