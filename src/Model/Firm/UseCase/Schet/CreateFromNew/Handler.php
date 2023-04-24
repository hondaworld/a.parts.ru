<?php

namespace App\Model\Firm\UseCase\Schet\CreateFromNew;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Firm\Service\SchetEmailService;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;
use App\ReadModel\Firm\SchetFetcher;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;
    private ExpenseDocumentRepository $expenseDocumentRepository;
    private SchetFetcher $schetFetcher;
    private FinanceTypeRepository $financeTypeRepository;
    private SchetEmailService $mailer;

    public function __construct(
        SchetRepository           $schetRepository,
        SchetFetcher              $schetFetcher,
        ExpenseDocumentRepository $expenseDocumentRepository,
        FinanceTypeRepository     $financeTypeRepository,
        SchetEmailService         $mailer,
        Flusher                   $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->schetFetcher = $schetFetcher;
        $this->financeTypeRepository = $financeTypeRepository;
        $this->mailer = $mailer;
    }

    public function handle(Command $command, User $user): array
    {
        $messages = [];

        if (!$schet = $this->schetRepository->findNewByUser($user)) {
            throw new DomainException('Счет отсутствует');
        }

        if (count($schet->getOrderGoods()) == 0) {
            throw new DomainException('Детали в счете отсутствуют');
        }

        $financeType = $this->financeTypeRepository->get($command->finance_typeID);
        if ($financeType->isBeznal()) {
            $expenseDocument = $this->expenseDocumentRepository->getOrCreate($user);

            if (!$expenseDocument->getExpFirm()) {
                throw new DomainException('Пожалуйста, выберите предприятие на закладке "Документ"');
            }
            if (!$expenseDocument->getExpFirmContact()) {
                throw new DomainException('Пожалуйста, выберите адрес предприятия на закладке "Документ"');
            }
            if (!$expenseDocument->getExpFirmBeznal()) {
                throw new DomainException('Пожалуйста, выберите реквизит предприятия на закладке "Документ"');
            }
            if (!$expenseDocument->getExpUser()) {
                throw new DomainException('Пожалуйста, выберите клиента на закладке "Документ"');
            }
            if (!$expenseDocument->getExpUserContact()) {
                throw new DomainException('Пожалуйста, выберите адрес клиента на закладке "Документ"');
            }

            $schet->attachGoodsFromOrderGoods();
            $firm = $expenseDocument->getExpFirm();
            $schet_num = $this->getSchetNum($firm, $command->finance_typeID);

            $schet->fromNewToNotPaid($schet_num, $command->document_prefix, $command->document_sufix, $expenseDocument);

            $messages[] = [
                'type' => 'success',
                'message' => 'Счет выставлен'
            ];
        } elseif ($financeType->isCreditCard()) {

            $firm = $financeType->getFirm();
            $schet->attachGoodsFromOrderGoods();
            $schet_num = $this->getSchetNum($firm, $command->finance_typeID);

            $schet->fromNewToNotPaidForCreditCard($schet_num, $command->document_prefix, $command->document_sufix, $firm, $user, $financeType);

            $messages[] = [
                'type' => 'success',
                'message' => 'Счет выставлен'
            ];

        } else {
            throw new DomainException('Указан неверный тип счета');
        }

        if ($command->isEmail) {
            $email = $this->mailer->emailWithUrl($schet);
            $messages[] = [
                'type' => 'success',
                'message' => 'Счет отправлен на ' . $email
            ];
        }

        $this->flusher->flush();

        return $messages;
    }

    private function getSchetNum(Firm $firm, int $finance_typeID): int
    {
        if ($this->schetFetcher->isExist($firm->getId())) {
            $schet_num = $this->schetFetcher->getNextNumber($firm->getId(), $finance_typeID);
        } else {
            $schet_num = $firm->getFirstSchet() ?: 1;
            if ($schet_num < 1) $schet_num = 1;
        }
        return $schet_num;
    }
}
