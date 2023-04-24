<?php


namespace App\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class StandartActionsVoter extends Voter
{
    public const INDEX = 'index';
    public const SHOW = 'show';
    public const EDIT = 'edit';
    public const CREATE = 'create';
    public const DELETE = 'delete';
    public const HIDE = 'hide';
    public const UNHIDE = 'unhide';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [self::INDEX, self::SHOW, self::EDIT, self::CREATE, self::DELETE, self::HIDE, self::UNHIDE], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

//        if ($this->security->isGranted('ROLE_ADMIN')) {
//            return true;
//        }

        return $manager->getActionByEntity($subject, $attribute);
    }
}