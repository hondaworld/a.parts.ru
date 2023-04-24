<?php


namespace App\Security\Voter\Firm;


use App\Model\Firm\Entity\Firm\Firm;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SchetVoter extends Voter
{

    public const SCHET_CHANGE_DOCUMENT = 'schet_change_document';
    public const SCHET_PAY = 'schet_pay';
    public const SCHET_CANCEL = 'schet_cancel';
    public const SCHET_SMS_SEND = 'schet_sms_send';
    public const SCHET_EMAIL_SEND = 'schet_email_send';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::SCHET_CHANGE_DOCUMENT,
                self::SCHET_PAY,
                self::SCHET_CANCEL,
                self::SCHET_SMS_SEND,
                self::SCHET_EMAIL_SEND
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('Schet', $attribute);
    }
}