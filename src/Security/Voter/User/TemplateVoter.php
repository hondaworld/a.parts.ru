<?php


namespace App\Security\Voter\User;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TemplateVoter extends Voter
{
    public const TEMPLATE_GROUP_CREATE = 'template_group_create';
    public const TEMPLATE_GROUP_EDIT = 'template_group_edit';
    public const TEMPLATE_GROUP_DELETE = 'template_group_delete';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::TEMPLATE_GROUP_CREATE,
                self::TEMPLATE_GROUP_EDIT,
                self::TEMPLATE_GROUP_DELETE
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('Template', $attribute);
    }
}