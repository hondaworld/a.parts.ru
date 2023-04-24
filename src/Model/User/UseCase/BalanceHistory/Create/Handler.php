<?php

namespace App\Model\User\UseCase\BalanceHistory\Create;

use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\User;
use App\Service\Email\EmailSender;
use App\Service\Sms\SmsSender;

class Handler
{
    private Flusher $flusher;
    private FinanceTypeRepository $financeTypeRepository;
    private SchetRepository $schetRepository;
    private SmsSender $smsSender;
    private TemplateRepository $templateRepository;
    private EmailSender $emailSender;

    public function __construct(
        FinanceTypeRepository $financeTypeRepository,
        SchetRepository       $schetRepository,
        SmsSender             $smsSender,
        TemplateRepository    $templateRepository,
        EmailSender           $emailSender,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $this->financeTypeRepository = $financeTypeRepository;
        $this->schetRepository = $schetRepository;
        $this->smsSender = $smsSender;
        $this->templateRepository = $templateRepository;
        $this->emailSender = $emailSender;
    }

    public function handle(Command $command, User $user, Manager $manager): array
    {
        $messages = [];

        $user->creditBySchet(
            $command->balance,
            $this->financeTypeRepository->get($command->finance_typeID),
            $manager,
            $command->description ?: '',
            $command->schetID ? $this->schetRepository->get($command->schetID) : null
        );

        if ($command->isSend) {

            if ($user->isSms()) {
                $this->smsSender->sendFromParts($manager, $user, (
                $this->templateRepository->get(Template::SMS_PAY_CONFIRM))->getText(['summ' => $command->balance])
                );

                $messages[] = [
                    'type' => 'success',
                    'message' => 'SMS отправлено на ' . $user->getPhonemob()
                ];

            } else {
                $messages[] = [
                    'type' => 'error',
                    'message' => 'Клиент запретил отправлять SMS'
                ];
            }

            $email = $user->getEmail()->getValueWithCheck();
            if (!$email) {
                $messages[] = [
                    'type' => 'error',
                    'message' => 'E-mail не отправлен'
                ];
            } else {

                $template = $this->templateRepository->get(Template::EMAIL_PAY_CONFIRM);
                $this->emailSender->send($user, $template->getSubject(), $template->getText(['summ' => $command->balance]));

                $messages[] = [
                    'type' => 'success',
                    'message' => 'E-mail отправлен на ' . $email
                ];

            }
        }

        $messages[] = [
            'type' => 'success',
            'message' => 'Платеж добавлен'
        ];

        $comment = "Добавление платежа $command->balance руб.";
        $manager->assignOrderOperation($user, null, $comment);
        $this->flusher->flush();

        return $messages;
    }
}
