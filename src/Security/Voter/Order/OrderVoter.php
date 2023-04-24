<?php


namespace App\Security\Voter\Order;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderVoter extends Voter
{
    public const ORDER_GOOD_CREATE = 'order_good_create';
    public const ORDER_GOOD_CHANGE_PRICE = 'order_good_change_price';
    public const ORDER_GOOD_SCHET = 'order_good_schet';
    public const ORDER_GOOD_DELETE_ALERT = 'order_good_delete_alert';
    public const ORDER_GOOD_CHANGE_LOCATION = 'order_good_change_location';
    public const ORDER_GOOD_SCHET_CREATE = 'order_good_schet_create';
    public const ORDER_SMS = 'order_sms';
    public const ORDER_CHANGE_DATES = 'order_change_dates';
    public const ORDER_GOOD_RESERVE = 'order_good_reserve';
    public const ORDER_GOOD_EXPENSE = 'order_good_expense';
    public const ORDER_RETURN_DOCUMENT = 'order_return_document';
    public const ORDER_GOOD_CHANGE_QUANTITY = 'order_good_change_quantity';
    public const ORDER_PICK = 'order_pick';
    public const ORDER_GOOD_DELETE = 'order_good_delete';
    public const ORDER_GOOD_REFUSE = 'order_good_refuse';
    public const ORDER_GOOD_LABELS = 'order_good_labels';
    public const ORDER_CONFIRM = 'order_confirm';
    public const MANAGER_ORDER_OPERATIONS = 'manager_order_operations';
    public const USER_SMS_HISTORY = 'user_sms_history';
    public const USER_COMMENTS = 'user_comments';
    public const ORDER_SHIPPING = 'order_shipping';
    public const ORDER_PAID = 'order_paid';
    public const ORDER_CHECK = 'order_check';
    public const ORDER_CHECK_DELETE = 'order_check_delete';
    public const ORDER_EXPENSE_DOCUMENT = 'order_expense_document';
    public const ORDER_NEW_DELETE = 'order_new_delete';

    private AuthorizationCheckerInterface $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
                self::ORDER_GOOD_CREATE,
                self::ORDER_GOOD_CHANGE_PRICE,
                self::ORDER_GOOD_SCHET,
                self::ORDER_GOOD_DELETE_ALERT,
                self::ORDER_GOOD_CHANGE_LOCATION,
                self::ORDER_GOOD_SCHET_CREATE,
                self::ORDER_SMS,
                self::ORDER_CHANGE_DATES,
                self::ORDER_GOOD_RESERVE,
                self::ORDER_GOOD_EXPENSE,
                self::ORDER_RETURN_DOCUMENT,
                self::ORDER_GOOD_CHANGE_QUANTITY,
                self::ORDER_PICK,
                self::ORDER_GOOD_DELETE,
                self::ORDER_GOOD_REFUSE,
                self::ORDER_GOOD_LABELS,
                self::ORDER_CONFIRM,
                self::MANAGER_ORDER_OPERATIONS,
                self::USER_SMS_HISTORY,
                self::USER_COMMENTS,
                self::ORDER_SHIPPING,
                self::ORDER_PAID,
                self::ORDER_CHECK,
                self::ORDER_CHECK_DELETE,
                self::ORDER_EXPENSE_DOCUMENT,
                self::ORDER_NEW_DELETE
            ], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $manager = $token->getUser();

        if (!$manager instanceof UserInterface) {
            return false;
        }

        return $manager->getActionByEntity('Order', $attribute);
    }
}