<?php

namespace App\Model\Order\UseCase\Good\SmsWarehouse;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\User;
use App\Service\Sms\SmsSender;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private SmsSender $smsSender;
    private TemplateRepository $templateRepository;

    public function __construct(
        SmsSender             $smsSender,
        TemplateRepository    $templateRepository,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $this->smsSender = $smsSender;
        $this->templateRepository = $templateRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        if (!$user->isSms()) {
            throw new DomainException('Пользователь запретил отсылать SMS');
        }

        $this->smsSender->sendFromParts($manager, $user, ($this->templateRepository->get($command->templateID))->getText());
        $user->assignUserComment($manager, 'Отправлена SMS с приходом на склад.');
        $manager->assignOrderOperation($user, null, "Отправка SMS с приходом на склад");

        $this->flusher->flush();
    }
}
