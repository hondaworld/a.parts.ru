<?php

namespace App\ReadModel\Order;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Shipping\Shipping;

class ShippingView
{
    public string $id;
    public string $userID;
    public string $user_name;
    public string $gruz_user_name = '';
    public string $gruz_user_town = '';
    public string $gruz_firm_name = '';
    public string $pay_type_name;
    public string $status;
    public string $status_name;
    public string $delivery_tk;
    public string $tracknumber;
    public \DateTime $dateofadded;
    public array $places;
    public string $nakladnaya;

    public function __construct(Shipping $shipping)
    {
        $this->id = $shipping->getId();
        $this->userID = $shipping->getUser()->getId();
        $this->user_name = $shipping->getUser()->getName();
        $this->setPayType($shipping->getPayType());
        $this->setGruz($shipping->getExpenseDocument());
        $this->status = $shipping->getStatus()->getId();
        $this->status_name = $shipping->getStatus()->getName();
        $this->delivery_tk = $shipping->getDeliveryTk() ? $shipping->getDeliveryTk()->getName() : '';
        $this->tracknumber = $shipping->getTracknumber();
        $this->dateofadded = $shipping->getDateofadded();
        $this->places = $shipping->getPlaces();
        $this->nakladnaya = $shipping->getNakladnaya();
    }

    public function setGruz(ExpenseDocument $expenseDocument)
    {
        if ($expenseDocument->getGruzFirmcontr()) {
            $this->gruz_user_name = $expenseDocument->getGruzFirmcontr()->getUr()->getOrganization();
            $this->gruz_user_town = $expenseDocument->getGruzFirmcontr()->getTown()->getNameWithRegion();
        } else {
            if ($expenseDocument->getGruzUser()) {
                $user = $expenseDocument->getGruzUser();
                $contact = $expenseDocument->getGruzUserContact();
            } else {
                $user = $expenseDocument->getExpUser();
                $contact = $expenseDocument->getExpUserContact();
            }

            if ($user) {
                $this->gruz_user_name = $user->getFullNameOrOrganization();
                if (!$contact) {
                    $contact = $user->getMainContact();
                }
                if ($contact) {
                    $this->gruz_user_town = $contact->getTown()->getNameWithRegion();
                }
            }
        }

        if ($expenseDocument->getGruzFirm()) {
            $firm = $expenseDocument->getGruzFirm();
        } else {
            $firm = $expenseDocument->getExpFirm();
        }

        if ($firm) {
            $this->gruz_firm_name = $firm->getNameShort();
        }
    }

    public function setPayType(int $payType)
    {
        $this->pay_type_name = $payType == 1 ? "Отправитель" : ($payType == 2 ? "Получатель" : '');
    }

    public function isEqualGruzUserTown(string $town): bool
    {
        return mb_strpos(mb_strtolower($this->gruz_user_town), mb_strtolower($town)) !== false;
    }
}