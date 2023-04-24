<?php

namespace App\Model\Expense\Service;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFakPrint\From;
use App\Model\Expense\Entity\SchetFakPrint\FromGruz;
use App\Model\Expense\Entity\SchetFakPrint\SchetFakPrint;
use App\Model\Expense\Entity\SchetFakPrint\SchetFakPrintRepository;
use App\Model\Expense\Entity\SchetFakPrint\To;
use App\Model\Expense\Entity\SchetFakPrint\ToCash;
use App\Model\Expense\Entity\SchetFakPrint\ToGruz;
use App\Model\Flusher;

class SchetFakPrintService
{
    private SchetFakPrintRepository $schetFakPrintRepository;
    private Flusher $flusher;

    public function __construct(
        SchetFakPrintRepository $schetFakPrintRepository,
        Flusher                 $flusher
    )
    {
        $this->schetFakPrintRepository = $schetFakPrintRepository;
        $this->flusher = $flusher;
    }

    public function getSchetFak(SchetFak $schetFak): SchetFakPrint
    {
        if ($schetFak->getSchetFakPrint()) {
            return $schetFak->getSchetFakPrint();
        }

        $expenseDocument = $schetFak->getExpenseDocument();

        $from = $this->from($expenseDocument);
        $from_gruz = $this->fromGruz($expenseDocument, $from);
        $to = $this->to($expenseDocument);
        $to_gruz = $this->toGruz($expenseDocument, $to);
        $to_cash = $this->toCash($expenseDocument, $to, $to_gruz);

        $expenseDocumentPrint = new SchetFakPrint(
            $schetFak,
            $from,
            $from_gruz,
            $to,
            $to_gruz,
            $to_cash
        );
        $this->schetFakPrintRepository->add($expenseDocumentPrint);
        $this->flusher->flush();
        return $expenseDocumentPrint;
    }

    private function from(ExpenseDocument $expenseDocument): From
    {
        $name = $expenseDocument->getExpFirm()->getName();
        $address = $expenseDocument->getExpFirmContact() ? $expenseDocument->getExpFirmContact()->getFullAddressWithZip() : '';
        $inn = $expenseDocument->getExpFirm()->getInn();
        $kpp = $expenseDocument->getExpFirm()->getKpp();
        return new From($name, $address, $inn, $kpp);
    }

    private function fromGruz(ExpenseDocument $expenseDocument, From $from): FromGruz
    {
        if (!$expenseDocument->getGruzFirm()) {
            return new FromGruz($from->getName(), $from->getAddress());
        } else {
            $name = $expenseDocument->getGruzFirm()->getName();
            $address = $expenseDocument->getGruzFirmContact() ? $expenseDocument->getGruzFirmContact()->getFullAddressWithZip() : '';
            return new FromGruz($name, $address);
        }
    }

    private function to(ExpenseDocument $expenseDocument): To
    {
        $name = $expenseDocument->getExpUser()->getPassportNameOrOrganization(true);
        $address = $expenseDocument->getExpUserContact() ? $expenseDocument->getExpUserContact()->getFullAddressWithZip() : '';
        return new To($name, $address);
    }

    private function toGruz(ExpenseDocument $expenseDocument, To $to): ToGruz
    {
        if ($expenseDocument->getGruzFirmcontr()) {
            $name = $expenseDocument->getGruzFirmcontr()->getUr()->getOrganizationWithInnAndKpp();
            $address = $expenseDocument->getGruzFirmcontr()->getFullAddressWithZip();
            $inn = $expenseDocument->getGruzFirmcontr()->getUr()->getInn();
            $kpp = $expenseDocument->getGruzFirmcontr()->getUr()->getKpp();
            return new ToGruz($name, $address, $inn, $kpp);
        }
        if ($expenseDocument->getGruzUser()) {
            $name = $expenseDocument->getGruzUser()->getPassportNameOrOrganization(true);
            $address = $expenseDocument->getGruzUserContact() ? $expenseDocument->getGruzUserContact()->getFullAddressWithZip() : '';
            $inn = $expenseDocument->getGruzUser()->getUr()->getInn();
            $kpp = $expenseDocument->getGruzUser()->getUr()->getKpp();
            return new ToGruz($name, $address, $inn, $kpp);
        }
        return new ToGruz($to->getName(), $to->getAddress());
    }

    private function toCash(ExpenseDocument $expenseDocument, To $to, ToGruz $toGruz): ToCash
    {
        if ($expenseDocument->getCashFirmcontr()) {
            $name = $expenseDocument->getCashFirmcontr()->getUr()->getOrganizationWithInnAndKpp();
            $address = $expenseDocument->getCashFirmcontr()->getFullAddressWithZip();
            $inn = $expenseDocument->getCashFirmcontr()->getUr()->getInn();
            $kpp = $expenseDocument->getCashFirmcontr()->getUr()->getKpp();

            if (($expenseDocument->isGruzInnKpp()) && ($toGruz->getInnGruz() != "")) {
                $inn = $toGruz->getInnGruz();
                $kpp = $toGruz->getKppGruz();
            }
            return new ToCash($name, $address, $inn, $kpp);
        }
        if ($expenseDocument->getCashUser()) {
            $name = $expenseDocument->getCashUser()->getPassportNameOrOrganization(true);
            $address = $expenseDocument->getCashUserContact() ? $expenseDocument->getCashUserContact()->getFullAddressWithZip() : '';
            $inn = $expenseDocument->getCashUser()->getUr()->getInn();
            $kpp = $expenseDocument->getCashUser()->getUr()->getKpp();

            if (($expenseDocument->isGruzInnKpp()) && ($toGruz->getInnGruz() != "")) {
                $inn = $toGruz->getInnGruz();
                $kpp = $toGruz->getKppGruz();
            }
            return new ToCash($name, $address, $inn, $kpp);
        }
        return new ToCash($to->getName(), $to->getAddress());
    }
}