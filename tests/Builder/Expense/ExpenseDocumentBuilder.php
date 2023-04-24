<?php

namespace App\Tests\Builder\Expense;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Beznal\FirmBeznalBuilder;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\FirmContactBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;

class ExpenseDocumentBuilder
{
    private User $user;

    private ?User $expUser = null;
    private ?Contact $expUserContact = null;
    private ?Beznal $expUserBeznal = null;

    private ?User $getterUser = null;
    private ?Contact $getterUserContact = null;
    private ?Beznal $getterUserBeznal = null;

    private ?FirmContr $getterFirmcontr = null;

    private ?User $cashierUser = null;
    private ?Contact $cashierUserContact = null;
    private ?Beznal $cashierUserBeznal = null;

    private ?FirmContr $cashierFirmcontr = null;

    private ?Firm $firm = null;

    private ?Firm $expFirm = null;
    private ?Contact $expFirmContact = null;
    private ?Beznal $expFirmBeznal = null;

    private ?Firm $getterFirm = null;
    private ?Contact $getterFirmContact = null;
    private ?Beznal $getterFirmBeznal = null;

    public function __construct(?User $user = null)
    {
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = (new UserBuilder())->build();
        }
    }

    public function withExpUser(?User $user, ?Contact $contact, ?Beznal $beznal): self
    {
        $clone = clone $this;

        $clone->expUser = $user;
        $clone->expUserContact = $contact;
        $clone->expUserBeznal = $beznal;

        return $clone;
    }

    public function withGetterUser(?User $user, ?Contact $contact, ?Beznal $beznal): self
    {
        $clone = clone $this;

        $clone->getterUser = $user;
        $clone->getterUserContact = $contact;
        $clone->getterUserBeznal = $beznal;

        return $clone;
    }

    public function withGetterFirmcontr(?Firmcontr $firmContr): self
    {
        $clone = clone $this;

        $clone->getterFirmcontr = $firmContr;

        return $clone;
    }

    public function withCashierUser(?User $user, ?Contact $contact, ?Beznal $beznal): self
    {
        $clone = clone $this;

        $clone->cashierUser = $user;
        $clone->cashierUserContact = $contact;
        $clone->cashierUserBeznal = $beznal;

        return $clone;
    }

    public function withCashierFirmcontr(?Firmcontr $firmContr): self
    {
        $clone = clone $this;

        $clone->cashierFirmcontr = $firmContr;

        return $clone;
    }

    public function withFirm(?Firm $firm): self
    {
        $clone = clone $this;

        $clone->firm = $firm;

        return $clone;
    }

    public function withExpFirm(?Firm $firm, ?Contact $contact, ?Beznal $beznal): self
    {
        $clone = clone $this;

        $clone->expFirm = $firm;
        $clone->expFirmContact = $contact;
        $clone->expFirmBeznal = $beznal;

        return $clone;
    }

    public function withGetterFirm(?Firm $firm, ?Contact $contact, ?Beznal $beznal): self
    {
        $clone = clone $this;

        $clone->getterFirm = $firm;
        $clone->getterFirmContact = $contact;
        $clone->getterFirmBeznal = $beznal;

        return $clone;
    }

    public function build(): ExpenseDocument
    {
        $expenseDocument = new ExpenseDocument($this->user);

        if ($this->expUser) {
            $expenseDocument->updateExpUser($this->expUser, $this->expUserContact, $this->expUserBeznal);
        }

        if ($this->getterUser) {
            $expenseDocument->updateGetter($this->getterUser, $this->getterUserContact, $this->getterUserBeznal);
        } elseif ($this->getterFirmcontr) {
            $expenseDocument->updateGetterFirmContr($this->getterFirmcontr);
        }

        if ($this->cashierUser) {
            $expenseDocument->updateGetter($this->cashierUser, $this->cashierUserContact, $this->cashierUserBeznal);
        } elseif ($this->cashierFirmcontr) {
            $expenseDocument->updateCashierFirmContr($this->cashierFirmcontr);
        }

        if ($this->firm) {
            $expenseDocument->updateFirm($this->firm);
        }

        if ($this->expFirm) {
            $expenseDocument->updateExpFirm($this->expFirm, $this->expFirmContact, $this->expFirmBeznal);
        }

        if ($this->getterFirm) {
            $expenseDocument->updateGruzFirm($this->getterFirm, $this->getterFirmContact, $this->getterFirmBeznal);
        }

        return $expenseDocument;
    }
}