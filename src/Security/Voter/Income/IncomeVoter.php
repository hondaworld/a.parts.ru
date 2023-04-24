<?php


namespace App\Security\Voter\Income;


use App\Model\Provider\Entity\Provider\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class IncomeVoter extends Voter
{
    public const INCOME_DATE_IN_PLAN = 'income_date_in_plan';
    public const INCOME_STATUS = 'income_status';
    public const INCOME_INSERT_DOCUMENT = 'income_insert_document';
    public const INCOME_RETURN_DOCUMENT = 'income_return_document';
    public const INCOME_WRITE_OFF_DOCUMENT = 'income_writeoff_document';
    public const INCOME_UNPACK = 'income_unpack';
    public const INCOME_QUANTITY = 'income_quantity';
    public const INCOME_ZAP_SKLAD = 'income_zapSklad';
    public const INCOME_CHECK_PRICE = 'income_check_price';
    public const INCOME_LABELS = 'income_labels';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::INCOME_DATE_IN_PLAN,
                self::INCOME_STATUS,
                self::INCOME_INSERT_DOCUMENT,
                self::INCOME_UNPACK,
                self::INCOME_QUANTITY,
                self::INCOME_RETURN_DOCUMENT,
                self::INCOME_WRITE_OFF_DOCUMENT,
                self::INCOME_ZAP_SKLAD,
                self::INCOME_CHECK_PRICE,
                self::INCOME_LABELS
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('Income', $attribute);
    }
}