<?php

namespace App\Model\Income\UseCase\Order\Mail;

use App\Model\Flusher;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Sklad\IncomeSkladRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistory;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistoryRepository;
use App\Model\Income\Service\IncomeOrder\IncomeOrderExcelPriceFabric;
use App\Model\Manager\Entity\Manager\Manager;
use App\Service\Email\EmailSender;
use DomainException;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;

class Handler
{
    private Flusher $flusher;
    private IncomeSkladRepository $incomeSkladRepository;
    private Swift_Mailer $mailer;
    private IncomeStatusRepository $incomeStatusRepository;
    private IncomeStatusHistoryRepository $incomeStatusHistoryRepository;
    private EmailSender $emailSender;

    public function __construct(
        Swift_Mailer                  $mailer,
        EmailSender                   $emailSender,
        IncomeSkladRepository         $incomeSkladRepository,
        IncomeStatusRepository        $incomeStatusRepository,
        IncomeStatusHistoryRepository $incomeStatusHistoryRepository,
        Flusher                       $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeSkladRepository = $incomeSkladRepository;
        $this->mailer = $mailer;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->incomeStatusHistoryRepository = $incomeStatusHistoryRepository;
        $this->emailSender = $emailSender;
    }

    public function handle(IncomeOrder $incomeOrder, string $price_directory, Manager $manager): void
    {
        if (!$incomeOrder->getIncomes()) {
            throw new DomainException('У заказа нет приходов');
        }

        if ($incomeOrder->getProvider()->getIncomeOrderEmail() == "") {
            throw new DomainException('E-mail у поставщика не введен');
        }

        $path = IncomeOrderExcelPriceFabric::get($incomeOrder, $price_directory)->create();

        $email = $incomeOrder->getProvider()->getIncomeOrderEmail();
        $subject = $incomeOrder->getMailSubject();
        $text = $incomeOrder->getMailText();

//        $message = (new Swift_Message($subject))
//            ->setFrom('order@parts.ru')
//            ->setTo($email)
//            ->setBody($text, 'text/html')
//            ->attach(Swift_Attachment::fromPath($path));

//        $this->mailer->send($message);

        $attachments = [
            [
                'body' => file_get_contents($path),
                'fileName' => basename($path)
            ]
        ];

//        $email = 'info@hondaworld.ru';
        $this->emailSender->sendEmail($email, $subject, $text, $attachments, 'order@parts.ru');

        if ($incomeOrder->isNotOrdered()) {
            foreach ($incomeOrder->getIncomes() as $income) {
                $income->getOneSkladOrCreate($incomeOrder->getZapSklad());
                $incomeStatus = $this->incomeStatusRepository->get(IncomeStatus::ORDERED);
                $income->updateStatus($incomeStatus, $manager);
                $income->updateDateOfZakaz(new \DateTime());
            }
            $incomeOrder->ordered();
        }

        $this->flusher->flush();
    }
}
