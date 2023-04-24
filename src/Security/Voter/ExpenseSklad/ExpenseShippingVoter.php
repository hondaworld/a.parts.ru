<?php


namespace App\Security\Voter\ExpenseSklad;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ExpenseShippingVoter extends Voter
{
    public const EXPENSE_SHIPPING_PACK = 'expense_shipping_pack';
    public const EXPENSE_SHIPPING_DELETE = 'expense_shipping_delete';
    public const EXPENSE_SHIPPING_SCAN = 'expense_shipping_scan';
    public const EXPENSE_SHIPPING_SEND = 'expense_shipping_send';
    public const EXPENSE_INCOME_INCOME = 'expense_income_income';
    public const EXPENSE_INCOME_SCAN = 'expense_income_scan';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [
                self::EXPENSE_SHIPPING_PACK,
                self::EXPENSE_SHIPPING_DELETE,
                self::EXPENSE_SHIPPING_SCAN,
                self::EXPENSE_SHIPPING_SEND,
                self::EXPENSE_INCOME_INCOME,
                self::EXPENSE_INCOME_SCAN,
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('ExpenseSklad', $attribute);
    }
}