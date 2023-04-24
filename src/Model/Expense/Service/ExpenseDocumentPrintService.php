<?php

namespace App\Model\Expense\Service;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint;
use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrintRepository;
use App\Model\Expense\Entity\DocumentPrint\From;
use App\Model\Expense\Entity\DocumentPrint\FromGruz;
use App\Model\Expense\Entity\DocumentPrint\To;
use App\Model\Expense\Entity\DocumentPrint\ToCash;
use App\Model\Expense\Entity\DocumentPrint\ToGruz;
use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;

class ExpenseDocumentPrintService
{
    private ExpenseDocumentPrintRepository $expenseDocumentPrintRepository;
    private SchetRepository $schetRepository;
    private Flusher $flusher;

    public function __construct(
        ExpenseDocumentPrintRepository $expenseDocumentPrintRepository,
        SchetRepository                $schetRepository,
        Flusher                        $flusher
    )
    {
        $this->expenseDocumentPrintRepository = $expenseDocumentPrintRepository;
        $this->schetRepository = $schetRepository;
        $this->flusher = $flusher;
    }

    public function getCheck(ExpenseDocument $expenseDocument): ExpenseDocumentPrint
    {
        if ($expenseDocument->getExpenseDocumentPrint()) {
            return $expenseDocument->getExpenseDocumentPrint();
        }

        $from = $this->fromCheck($expenseDocument);
        $to = $this->toCheck($expenseDocument);

        $expenseDocumentPrint = new ExpenseDocumentPrint(
            $expenseDocument,
            '',
            '',
            '',
            $from,
            new FromGruz('', ''),
            $to,
            new ToGruz('', ''),
            new ToCash('', '')
        );
        $this->expenseDocumentPrintRepository->add($expenseDocumentPrint);
        $this->flusher->flush();
        return $expenseDocumentPrint;
    }

    public function getNakladnaya(ExpenseDocument $expenseDocument, bool $isNotCreate = false): ExpenseDocumentPrint
    {
        if ($expenseDocument->getExpenseDocumentPrint()) {
            return $expenseDocument->getExpenseDocumentPrint();
        }

        $director = $expenseDocument->getExpFirm()->getDirector() ? $expenseDocument->getExpFirm()->getDirector()->getName() : '';
        $buhgalter = $expenseDocument->getExpFirm()->getBuhgalter() ? $expenseDocument->getExpFirm()->getBuhgalter()->getName() : '';

        $osn = $this->osn($expenseDocument);
        $from = $this->from($expenseDocument);
        $from_gruz = $this->fromGruz($expenseDocument, $from);
        $to = $this->to($expenseDocument);
        $to_gruz = $this->toGruz($expenseDocument, $to);
        $to_cash = $this->toCash($expenseDocument, $to);

        $expenseDocumentPrint = new ExpenseDocumentPrint(
            $expenseDocument,
            $director,
            $buhgalter,
            $osn,
            $from,
            $from_gruz,
            $to,
            $to_gruz,
            $to_cash
        );
        if (!$isNotCreate) {
            $this->expenseDocumentPrintRepository->add($expenseDocumentPrint);
            $this->flusher->flush();
        }
        return $expenseDocumentPrint;
    }

    public function osn(ExpenseDocument $expenseDocument): string
    {
        if ($expenseDocument->getOsn()->getOsnName()) {
            return $expenseDocument->getOsn()->getOsnName();
        }

        if ($expenseDocument->getExpUser()->getUr()->getOsnName()) {
            return $expenseDocument->getExpUser()->getUr()->getOsnName();
        }

        $schets = $this->schetRepository->findByExpenseDocument($expenseDocument);
        if ($schets) {
            $arr = [];
            foreach ($schets as $schet) {
                $arr[] = $schet->getOsnName();
            }
            return 'Счет ' . implode(', ', $arr);
        }

        return 'Основной договор';
    }

    private function fromCheck(ExpenseDocument $expenseDocument): From
    {
        $chek =
            $expenseDocument->getExpFirm()->getName() .
            ($expenseDocument->getExpFirm()->getInn() ? ', ИНН ' . $expenseDocument->getExpFirm()->getInn() : '') .
            ($expenseDocument->getExpFirm()->getKpp() ? ', КПП ' . $expenseDocument->getExpFirm()->getKpp() : '');
        return new From('', '', $chek);
    }

    private function from(ExpenseDocument $expenseDocument): From
    {
        $okpo = $expenseDocument->getExpFirm()->getOkpo();
        $chek =
            $expenseDocument->getExpFirm()->getName() .
            ($expenseDocument->getExpFirm()->getInn() ? ', ИНН ' . $expenseDocument->getExpFirm()->getInn() : '') .
            ($expenseDocument->getExpFirm()->getKpp() ? ', КПП ' . $expenseDocument->getExpFirm()->getKpp() : '');
        $nakladnaya =
            $chek .
            ($expenseDocument->getExpFirmContact() ? ', ' . $expenseDocument->getExpFirmContact()->getFullAddressWithPhones() : '') .
            ($expenseDocument->getExpFirmBeznal() ? ', ' . $expenseDocument->getExpFirmBeznal()->getFullRequisiteWithBik() : '');
        return new From($okpo, $nakladnaya, $chek);
    }

    private function fromGruz(ExpenseDocument $expenseDocument, From $from): FromGruz
    {
        if (!$expenseDocument->getGruzFirm()) {
            return new FromGruz($from->getOkpo(), $from->getNakladnaya());
        } else {
            $okpo = $expenseDocument->getGruzFirm()->getOkpo();
            $nakladnaya =
                $expenseDocument->getGruzFirm()->getName() .
                ($expenseDocument->getGruzFirm()->getInn() ? ', ИНН ' . $expenseDocument->getGruzFirm()->getInn() : '') .
                ($expenseDocument->getGruzFirm()->getKpp() ? ', КПП ' . $expenseDocument->getGruzFirm()->getKpp() : '') .
                ($expenseDocument->getGruzFirmContact() ? ', ' . $expenseDocument->getGruzFirmContact()->getFullAddressWithPhones() : '') .
                ($expenseDocument->getGruzFirmBeznal() ? ', ' . $expenseDocument->getGruzFirmBeznal()->getFullRequisiteWithBik() : '');
            return new FromGruz($okpo, $nakladnaya);
        }
    }

    private function toCheck(ExpenseDocument $expenseDocument): To
    {
        $chek = $expenseDocument->getUser()->getFullNameWithPhoneMobile();
        return new To('', '', $chek);
    }

    private function to(ExpenseDocument $expenseDocument): To
    {
        $okpo = $expenseDocument->getExpUser()->getUr()->getOkpo();
        $chek = $expenseDocument->getUser()->getFullNameWithPhoneMobile();

        $nakladnaya =
            $expenseDocument->getExpUser()->getPassportNameOrOrganizationWithPassport(true) .
            ($expenseDocument->getExpUserContact() ? ', ' . $expenseDocument->getExpUserContact()->getFullAddressWithPhones() : '');
        if ($expenseDocument->getExpUser()->getUr()->isUr()) {
            $nakladnaya .= ($expenseDocument->getExpUserBeznal() ? ', ' . $expenseDocument->getExpUserBeznal()->getFullRequisiteWithBik() : '');
        }
        return new To($okpo, $nakladnaya, $chek);
    }

    private function toGruz(ExpenseDocument $expenseDocument, To $to): ToGruz
    {
        if ($expenseDocument->getGruzFirmcontr()) {
            return new ToGruz(
                $expenseDocument->getGruzFirmcontr()->getUr()->getOkpo(),
                $expenseDocument->getGruzFirmcontr()->getUr()->getOrganizationWithInnAndKpp() . ', ' .
                $expenseDocument->getGruzFirmcontr()->getFullAddressWithPhones() . ', ' .
                $expenseDocument->getGruzFirmcontr()->getFullRequisiteWithBik()
            );
        }
        if ($expenseDocument->getGruzUser()) {
            $okpo = $expenseDocument->getGruzUser()->getUr()->getOkpo();

            $nakladnaya =
                $expenseDocument->getGruzUser()->getPassportNameOrOrganizationWithPassport(true) .
                ($expenseDocument->getGruzUserContact() ? ', ' . $expenseDocument->getGruzUserContact()->getFullAddressWithPhones() : '');
            if ($expenseDocument->getGruzUser()->getUr()->isUr()) {
                $nakladnaya .= ($expenseDocument->getGruzUserBeznal() ? ', ' . $expenseDocument->getGruzUserBeznal()->getFullRequisiteWithBik() : '');
            }
            return new ToGruz($okpo, $nakladnaya);
        }
        return new ToGruz($to->getOkpo(), $to->getNakladnaya());
    }

    private function toCash(ExpenseDocument $expenseDocument, To $to): ToCash
    {
        if ($expenseDocument->getCashFirmcontr()) {
            return new ToCash(
                $expenseDocument->getCashFirmcontr()->getUr()->getOkpo(),
                $expenseDocument->getCashFirmcontr()->getUr()->getOrganizationWithInnAndKpp() . ', ' .
                $expenseDocument->getCashFirmcontr()->getFullAddressWithPhones() . ', ' .
                $expenseDocument->getCashFirmcontr()->getFullRequisiteWithBik()
            );
        }
        if ($expenseDocument->getCashUser()) {
            $okpo = $expenseDocument->getCashUser()->getUr()->getOkpo();

            $nakladnaya =
                $expenseDocument->getCashUser()->getPassportNameOrOrganizationWithPassport(true) .
                ($expenseDocument->getCashUserContact() ? ', ' . $expenseDocument->getCashUserContact()->getFullAddressWithPhones() : '');
            if ($expenseDocument->getCashUser()->getUr()->isUr()) {
                $nakladnaya .= ($expenseDocument->getCashUserBeznal() ? ', ' . $expenseDocument->getCashUserBeznal()->getFullRequisiteWithBik() : '');
            }
            return new ToCash($okpo, $nakladnaya);
        }
        return new ToCash($to->getOkpo(), $to->getNakladnaya());
    }
}