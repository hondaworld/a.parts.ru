<?php

namespace App\Model\Expense\Service;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\User\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;

class ExpenseDocumentChecker
{
    private Request $request;
    private array $expenses;
    private ExpenseDocument $expenseDocument;
    private User $user;
    private float $sum;
    private float $financeTypeBalance;

    public function __construct(Request $request, array $expenses, ExpenseDocument $expenseDocument, User $user, float $sum, float $financeTypeBalance)
    {
        $this->request = $request;
        $this->expenses = $expenses;
        $this->expenseDocument = $expenseDocument;
        $this->user = $user;
        $this->sum = $sum;
        $this->financeTypeBalance = $financeTypeBalance;
    }

    public function check(): bool
    {
        if (!$this->user->getMainContact() || !$this->expenseDocument->getFinanceType() || !$this->expenseDocument->getFinanceType()->getFirm() || $this->expenseDocument->isSimpleCheck() === false) {
            return false;
        }
        if ($this->user->getUr()->isUr() && !$this->request->query->get('isUr')) {
            return false;
        }
//        if ($this->expenseDocument->getExpenseType() && $this->expenseDocument->getExpenseType()->isSms() && !$this->expenseDocument->isSmsCheck()) {
//            return false;
//        }
        if ($this->expenseDocument->isPicking()) {
            return false;
        }
        if (!$this->expenses) {
            return false;
        }
        if ($this->expenseDocument->isSimpleCheck() !== true) {
            return false;
        }
        if ($this->user->getBalanceLimit() == 0 && ($this->expenseDocument->isSimpleCheck() === null || $this->expenseDocument->isSimpleCheck() === true && $this->sum > $this->financeTypeBalance)) {
            return false;

        } elseif (!$this->user->isAllowBalanceForOrder($this->sum)) {
            return false;
        }
        return true;
    }

    public function torg12(): bool
    {
        if (!$this->expenseDocument->getExpFirm() || !$this->expenseDocument->getExpFirmContact() || !$this->expenseDocument->getExpFirmBeznal()) {
            return false;
        }

        if (!$this->expenseDocument->getGruzFirmForDocument() || !$this->expenseDocument->getGruzFirmContactForDocument() || !$this->expenseDocument->getGruzFirmBeznalForDocument()) {
            return false;
        }

        if (!$this->expenseDocument->getExpUser() || !$this->expenseDocument->getExpUserContact()) {
            return false;
        }

        if (!$this->expenseDocument->getGruzUserForDocument() || !$this->expenseDocument->getGruzUserContactForDocument()) {
            return false;
        }


        if ((!$this->expenseDocument->getGruzUserBeznalForDocument() || !$this->expenseDocument->getExpUserBeznal()) && $this->expenseDocument->isBeznal()) {
            return false;
        }

//        if ($this->expenseDocument->getExpenseType() && $this->expenseDocument->getExpenseType()->isSms() && !$this->expenseDocument->isSmsCheck()) {
//            return false;
//        }
        if ($this->expenseDocument->isPicking()) {
            return false;
        }
        if (!$this->expenses) {
            return false;
        }
        if ($this->expenseDocument->isSimpleCheck() !== false) {
            return false;
        }

        if ($this->user->getBalanceLimit() == 0 && ($this->expenseDocument->isSimpleCheck() === null || $this->expenseDocument->isSimpleCheck() === false && $this->sum > $this->financeTypeBalance)) {
            return false;
        } elseif (!$this->user->isAllowBalanceForOrder($this->sum)) {
            return false;
        }
        return true;
    }

    public function torg12Test(): bool
    {
        if (!$this->expenseDocument->getExpFirm() || !$this->expenseDocument->getExpFirmContact() || !$this->expenseDocument->getExpFirmBeznal()) {
            return false;
        }

        if (!$this->expenseDocument->getGruzFirmForDocument() || !$this->expenseDocument->getGruzFirmContactForDocument() || !$this->expenseDocument->getGruzFirmBeznalForDocument()) {
            return false;
        }

        if (!$this->expenseDocument->getExpUser() || !$this->expenseDocument->getExpUserContact()) {
            return false;
        }

        if (!$this->expenseDocument->getGruzUserForDocument() || !$this->expenseDocument->getGruzUserContactForDocument()) {
            return false;
        }


        if ((!$this->expenseDocument->getGruzUserBeznalForDocument() || !$this->expenseDocument->getExpUserBeznal()) && $this->expenseDocument->isBeznal()) {
            return false;
        }

//        if ($this->expenseDocument->getExpenseType() && $this->expenseDocument->getExpenseType()->isSms() && !$this->expenseDocument->isSmsCheck()) {
//            return false;
//        }
        if ($this->expenseDocument->isPicking()) {
            return false;
        }
        if (!$this->expenses) {
            return false;
        }
        if ($this->user->getBalanceLimit() == 0 && ($this->expenseDocument->isSimpleCheck() === null || $this->expenseDocument->isSimpleCheck() === false && $this->sum > $this->financeTypeBalance)) {
            return false;
        }
        return true;
    }
}