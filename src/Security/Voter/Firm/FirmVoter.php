<?php


namespace App\Security\Voter\Firm;


use App\Model\Firm\Entity\Firm\Firm;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FirmVoter extends Voter
{

    public const FIRM_CONTACTS = 'contacts';
    public const FIRM_CONTACTS_CHANGE = 'contacts_change';
    public const FIRM_BEZNALS = 'beznals';
    public const FIRM_BEZNALS_CHANGE = 'beznals_change';
    public const FIRM_DOCUMENTS = 'documents';
    public const FIRM_DOCUMENTS_CHANGE = 'documents_change';
    public const FIRM_MANAGERS = 'managers';
    public const FIRM_OTHERS = 'others';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::FIRM_CONTACTS,
                self::FIRM_CONTACTS_CHANGE,
                self::FIRM_BEZNALS,
                self::FIRM_BEZNALS_CHANGE,
                self::FIRM_DOCUMENTS,
                self::FIRM_DOCUMENTS_CHANGE,
                self::FIRM_MANAGERS,
                self::FIRM_OTHERS
            ], true) && $subject instanceof Firm;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Firm) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $manager->getActionByEntity('Firm', $attribute);
    }
}