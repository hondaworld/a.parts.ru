<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Create;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Expense\Entity\Document\Document;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFak\SchetFakRepository;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Shop\Entity\Discount\DiscountRepository;
use App\Model\User\Entity\EmailStatus\UserEmailStatus;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\User;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Expense\SchetFakFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\Service\Email\EmailSender;
use DomainException;
use Twig\Environment;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private ExpenseDocumentFetcher $expenseDocumentFetcher;
    private ShippingStatusRepository $shippingStatusRepository;
    private DocumentTypeRepository $documentTypeRepository;
    private TemplateRepository $templateRepository;
    private Environment $twig;
    private ZapCardRepository $zapCardRepository;
    private EmailSender $emailSender;
    private DiscountRepository $discountRepository;
    private OrderGoodFetcher $orderGoodFetcher;
    private NalogNdsRepository $nalogNdsRepository;
    private SchetFakFetcher $schetFakFetcher;
    private SchetFakRepository $schetFakRepository;

    public function __construct(
        OrderGoodRepository      $orderGoodRepository,
        OrderGoodFetcher         $orderGoodFetcher,
        ExpenseDocumentFetcher   $expenseDocumentFetcher,
        ShippingStatusRepository $shippingStatusRepository,
        DocumentTypeRepository   $documentTypeRepository,
        TemplateRepository       $templateRepository,
        ZapCardRepository        $zapCardRepository,
        Environment              $twig,
        EmailSender              $emailSender,
        DiscountRepository       $discountRepository,
        NalogNdsRepository       $nalogNdsRepository,
        SchetFakFetcher          $schetFakFetcher,
        SchetFakRepository       $schetFakRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->expenseDocumentFetcher = $expenseDocumentFetcher;
        $this->shippingStatusRepository = $shippingStatusRepository;
        $this->documentTypeRepository = $documentTypeRepository;
        $this->templateRepository = $templateRepository;
        $this->twig = $twig;
        $this->zapCardRepository = $zapCardRepository;
        $this->emailSender = $emailSender;
        $this->discountRepository = $discountRepository;
        $this->orderGoodFetcher = $orderGoodFetcher;
        $this->nalogNdsRepository = $nalogNdsRepository;
        $this->schetFakFetcher = $schetFakFetcher;
        $this->schetFakRepository = $schetFakRepository;
    }

    public function handle(Command $command, ExpenseDocument $expenseDocument, User $user, Manager $manager): void
    {
        if (!in_array($command->doc_typeID, [DocumentType::RN, DocumentType::TCH])) {
            throw new DomainException('Тип документа неверный');
        }
        $expenseDocument = $this->updateExpenseDocumentFirm($expenseDocument);

        $orderGoods = $this->orderGoodRepository->expenses($user);
        $zapCards = [];
        foreach ($orderGoods as $orderGood) {
            $zapCard = $this->zapCardRepository->findOneBy(['number' => $orderGood->getNumber(), 'creater' => $orderGood->getCreater()]);
            if ($zapCard) {
                $zapCards[$orderGood->getId()] = $zapCard->getDetailName();
            }
        }

        $sum_goods = $this->orderGoods($orderGoods, $expenseDocument, $manager);

        $document_num = $this->getDocumentNum($command->doc_typeID, $expenseDocument);
        $this->shipping($command->isShipping, $expenseDocument);
        $expenseDocument->done(new Document($document_num), $this->documentTypeRepository->get($command->doc_typeID), $manager, $command->isService);

        $this->mailOrder($user, $orderGoods, $zapCards, $sum_goods);

        $this->updateUserDiscount($user, $sum_goods);

        $user->debitByExpense($sum_goods, $document_num, $expenseDocument, $manager);

        if ($expenseDocument->isSimpleCheck() === false) {
            if ($user->getEmail()->getValueWithCheck() && !in_array(UserEmailStatus::DOCUMENT_SENT, $user->getExcludeEmailStatusIds())) {
                $expenseDocument->allowSentDocument();
            }
            $this->createSchetFak($expenseDocument);
        }

        $this->flusher->flush();
    }

    private function mailOrder(User $user, array $orderGoods, array $zapCards, int $sum_goods)
    {
        $userEmail = $user->getEmail()->getValueWithCheck();

        if ($userEmail && !in_array(UserEmailStatus::ORDER_SENT, $user->getExcludeEmailStatusIds())) {
            $template = $this->templateRepository->get(Template::ORDER_SENT);


            $table = $this->twig->render('app/orders/expenseDocument/mail/table.html.twig', [
                'orderGoods' => $orderGoods,
                'zapCards' => $zapCards,
                'sum_goods' => $sum_goods
            ]);

            $this->emailSender->send($user, $template->getSubject(), $template->getText([
                'name' => $user->getUserName()->getFirstname(),
                'table' => $table
            ]));
        }
    }

    /**
     * Обновление данных об организации
     *
     * @param ExpenseDocument $expenseDocument
     * @return ExpenseDocument
     */
    private function updateExpenseDocumentFirm(ExpenseDocument $expenseDocument): ExpenseDocument
    {
        if ($expenseDocument->isSimpleCheck() === true) {
            $firm = $expenseDocument->getFinanceType()->getFirm();
            $expenseDocument->updateExpFirm($firm, null, null);
            $expenseDocument->updateFirm($firm);
        } else {
            $expenseDocument->updateFirm($expenseDocument->getExpFirm());
        }
        $expenseDocument->reNewDateOfAdded();
        return $expenseDocument;
    }

    /**
     * Убираем резервы, отгружаем
     *
     * @param OrderGood[] $orderGoods
     * @param ExpenseDocument $expenseDocument
     * @param Manager $manager
     * @return int
     */
    private function orderGoods(array $orderGoods, ExpenseDocument $expenseDocument, Manager $manager): int
    {
        $sum_goods = 0;

        foreach ($orderGoods as $orderGood) {
            $sum_goods += $orderGood->getDiscountPrice() * $orderGood->getQuantity();

            foreach ($orderGood->getExpenses() as $expense) {
                $expense->expense($orderGood->getZapSklad());
            }

            $orderGood->clearAllEmailed();
            $orderGood->updateExpenseDocument($expenseDocument, $manager);
            $manager->assignOrderOperation(null, $orderGood->getOrder(), 'Отгрузка детали', $orderGood->getNumber()->getValue());
        }

        return $sum_goods;
    }

    /**
     * Получение номера накладной
     *
     * @param int $doc_typeID
     * @param ExpenseDocument $expenseDocument
     * @return int
     */
    private function getDocumentNum(int $doc_typeID, ExpenseDocument $expenseDocument): int
    {
        if ($doc_typeID == DocumentType::RN) {
            return $this->expenseDocumentFetcher->getNextRN($expenseDocument->getExpFirm());
        } else {
            return $this->expenseDocumentFetcher->getNextTCH($expenseDocument->getExpFirm());
        }
    }

    /**
     * Создание отгрузки
     *
     * @param bool $isShipping
     * @param ExpenseDocument $expenseDocument
     */
    private function shipping(bool $isShipping, ExpenseDocument $expenseDocument): void
    {
        $shippingStatus = $this->shippingStatusRepository->get(ShippingStatus::DOCUMENTS_DONE);
        if (count($expenseDocument->getShippings()) > 0) {
            $expenseDocument->updateShippingsStatus($shippingStatus);
        } else {
            if ($isShipping) {
                $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);
            }
        }
    }

    /**
     * Обновление скидок
     *
     * @param User $user
     * @param int $sum_goods
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateUserDiscount(User $user, int $sum_goods): void
    {
        if ($user->allowUpdateDiscount()) {
            $sum = $this->orderGoodFetcher->getSumByRetailUser($user->getId()) + $sum_goods;

            $discountParts = 0;
            $discountService = 0;

            $discounts = $this->discountRepository->findBy([], ['summ' => 'asc']);
            foreach ($discounts as $discount) {
                if ($sum > $discount->getSumm()) {
                    $discountParts = $discount->getDiscountSpare();
                    $discountService = $discount->getDiscountService();
                }
            }
            if ($user->getDiscountParts() > $discountParts) $discountParts = $user->getDiscountParts();
            if ($user->getDiscountService() > $discountService) $discountService = $user->getDiscountService();

            $user->updateDiscount($user->getSchetDays(), $discountParts, $discountService);
        }
    }

    private function createSchetFak(ExpenseDocument $expenseDocument): void
    {
        $nalogNds = $this->nalogNdsRepository->getLastByFirm($expenseDocument->getFirm());
        $document_num = $this->schetFakFetcher->getNext($expenseDocument->getFirm());

        $schetFak = new SchetFak($expenseDocument, new \App\Model\Expense\Entity\SchetFak\Document($document_num), $nalogNds->getNalog(), $expenseDocument->getFirm());
        $this->schetFakRepository->add($schetFak);
    }
}
