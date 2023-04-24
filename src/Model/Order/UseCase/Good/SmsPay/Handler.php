<?php

namespace App\Model\Order\UseCase\Good\SmsPay;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\User;
use App\Service\Sms\SmsSender;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private SmsSender $smsSender;
    private TemplateRepository $templateRepository;
    private OrderRepository $orderRepository;

    public function __construct(
        SmsSender             $smsSender,
        TemplateRepository    $templateRepository,
        OrderRepository       $orderRepository,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $this->smsSender = $smsSender;
        $this->templateRepository = $templateRepository;
        $this->orderRepository = $orderRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        if (!$user->isSms()) {
            throw new DomainException('Пользователь запретил отсылать SMS');
        }

        $this->smsSender->sendFromParts($manager, $user, (
        $this->templateRepository->get($command->templateID))->getText(
                [
                    'orderID' => $command->orderID,
                    'summ' => $command->sum
                ]
            )
        );

        $user->assignUserComment($manager, 'Отправлена SMS с оплатой на карту для заказа ' . $command->orderID . ' на сумму ' . $command->sum . '.');
        $manager->assignOrderOperation($user, $this->orderRepository->get($command->orderID), "Отправка SMS с оплатой на карту для заказа");

        $this->flusher->flush();
    }
}
