<?php

namespace App\Model\Order\UseCase\Good\Confirm;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\User;
use App\Service\Email\EmailSender;
use App\Service\Sms\SmsSender;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private SmsSender $smsSender;
    private TemplateRepository $templateRepository;
    private EmailSender $emailSender;

    public function __construct(
        OrderGoodRepository   $orderGoodRepository,
        SmsSender             $smsSender,
        TemplateRepository    $templateRepository,
        EmailSender           $emailSender,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->smsSender = $smsSender;
        $this->templateRepository = $templateRepository;
        $this->emailSender = $emailSender;
    }

    public function handle(Command $command, Manager $manager, User $user): array
    {
        $messages = [];

        $orders = [];

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);
            $orderID = $orderGood->getOrder()->getId();

            if ($orderGood->getZapSklad()) {
                if (!$orderGood->getZapCardReserve()) {
                    if (!in_array($orderID, $orders)) $orders[] = $orderID;
                }
            } elseif (!$orderGood->getIncome()) {
                if (!in_array($orderID, $orders)) $orders[] = $orderID;
            }
        }

        if (!$orders) {
            throw new DomainException("Подходящих заказов нет");
        }

        if ($user->isSms()) {
            $this->smsSender->sendFromParts($manager, $user, (
            $this->templateRepository->get(Template::SMS_ORDER_CONFIRM))->getText(
                    [
                        'orders' => implode(', ', $orders)
                    ]
                )
            );

            $messages[] = [
                'type' => 'success',
                'message' => 'SMS отправлено на ' . $user->getPhonemob()
            ];

            $comment = "Отправлена SMS с подтверждением заказов " . implode(", ", $orders);
            $user->assignUserComment($manager, $comment);
            $manager->assignOrderOperation($user, null, $comment);
        } else {
            $messages[] = [
                'type' => 'danger',
                'message' => 'Клиент запретил отправлять SMS'
            ];
        }
        if (empty($user->getEmail()->getValue())) {
            $messages[] = [
                'type' => 'danger',
                'message' => 'У клиента нет e-mail'
            ];
        } elseif (!$user->getEmail()->isActivated()) {
            $messages[] = [
                'type' => 'danger',
                'message' => 'E-mail клиента не активирован'
            ];
        } elseif (!$user->getEmail()->isNotification()) {
            $messages[] = [
                'type' => 'danger',
                'message' => 'Клиент запретил отсылать e-mail'
            ];
        } else {

            $template = $this->templateRepository->get(Template::EMAIL_ORDER_CONFIRM);

            $this->emailSender->send($user, $template->getSubject(), $template->getText(['orders' => implode(', ', $orders)]));

            $messages[] = [
                'type' => 'success',
                'message' => 'E-mail отправлен на ' . $user->getEmail()->getValue()
            ];

            $comment = "Отправлен E-mail с подтверждением заказов " . implode(", ", $orders);
            $user->assignUserComment($manager, $comment);
            $manager->assignOrderOperation($user, null, $comment);
        }
        $this->flusher->flush();

        return $messages;
    }
}
