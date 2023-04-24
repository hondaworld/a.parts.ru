<?php

namespace App\Model\Firm\UseCase\Schet\Pay;

use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertTypeRepository;
use App\Model\User\Entity\EmailStatus\UserEmailStatus;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Service\Email\EmailSender;
use App\Service\Sms\SmsSender;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;
    private SmsSender $smsSender;
    private TemplateRepository $templateRepository;
    private EmailSender $emailSender;
    private OrderAlertTypeRepository $orderAlertTypeRepository;

    public function __construct(
        SchetRepository          $schetRepository,
        SmsSender                $smsSender,
        TemplateRepository       $templateRepository,
        EmailSender              $emailSender,
        OrderAlertTypeRepository $orderAlertTypeRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
        $this->smsSender = $smsSender;
        $this->templateRepository = $templateRepository;
        $this->emailSender = $emailSender;
        $this->orderAlertTypeRepository = $orderAlertTypeRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $schet = $this->schetRepository->get($command->schetID);

        if (!$schet->isPayAllow()) {
            throw new DomainException('Статус должен быть "Ожидает оплаты"');
        }

        $messages = [];

        $user = $schet->getUser();

        $user->creditBySchet(
            $command->summ,
            $schet->getFinanceType(),
            $manager,
            '',
            $schet
        );

        $schet->pay($command->dateofpaid, $command->summ, $this->orderAlertTypeRepository->purchaseType());

        if ($command->isEmail) {
            if ($user->isSms()) {
                $this->smsSender->sendFromParts($manager, $user, (
                $this->templateRepository->get(Template::SMS_PAY_CONFIRM))->getText(['summ' => $command->summ])
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
            if (empty($user->getEmail()->getValue()) || !$user->getEmail()->isActivated() || !$user->getEmail()->isNotification() ||  in_array(UserEmailStatus::SCHET, $user->getExcludeEmailStatusIds())) {
                $messages[] = [
                    'type' => 'error',
                    'message' => 'E-mail не отправлен'
                ];
            } else {
                $template = $this->templateRepository->get(Template::EMAIL_PAY_CONFIRM);
                $this->emailSender->send($user, $template->getSubject(), $template->getText(['summ' => $command->summ]));

                $messages[] = [
                    'type' => 'success',
                    'message' => 'E-mail отправлен на ' . $user->getEmail()->getValue()
                ];
            }
        }

        $messages[] = [
            'type' => 'success',
            'message' => 'Счет оплачен'
        ];

        $this->flusher->flush();

        return $messages;
    }
}
