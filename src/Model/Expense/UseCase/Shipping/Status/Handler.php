<?php

namespace App\Model\Expense\UseCase\Shipping\Status;

use App\Model\Expense\Entity\Shipping\ShippingRepository;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private ShippingStatusRepository $shippingStatusRepository;
    private ShippingRepository $shippingRepository;

    public function __construct(
        ShippingStatusRepository      $shippingStatusRepository,
        ShippingRepository            $shippingRepository,
        Flusher                       $flusher
    )
    {
        $this->flusher = $flusher;
        $this->shippingStatusRepository = $shippingStatusRepository;
        $this->shippingRepository = $shippingRepository;
    }

    public function handle(Command $command): array
    {
        $messages = [];

        $statuses = $this->shippingStatusRepository->allByNumber();

        if ($command->cols) {
            foreach ($command->cols as $shippingID) {
                $shipping = $this->shippingRepository->get($shippingID);

                $status = $this->shippingStatusRepository->get($command->status);

                if ($status->getNumber() - $shipping->getStatus()->getNumber() != 1) {
                    foreach ($statuses as $item) {
                        if ($item->getNumber() == $status->getNumber() - 1) {
                            $messages[] = ['type' => 'error', 'message' => "У отгрузки " . $shipping->getUser()->getName() . " статус должен быть " . $item->getName()];
                        }
                    }
                } elseif ($status->getId() == ShippingStatus::SENT_STATUS && (!$shipping->getDeliveryTk() || $shipping->getTracknumber() == '')) {
                    $messages[] = ['type' => 'error', 'message' => "У отгрузки " . $shipping->getUser()->getName() . " отсутствует ТК или трекинг номер"];
                } else {
                    $shipping->updateStatus($status);
                    $messages[] = ['type' => 'success', 'message' => "У отгрузки " . $shipping->getUser()->getName() . " статус изменен на " . $status->getName()];
                }
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
