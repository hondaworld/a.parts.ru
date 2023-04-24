<?php

namespace App\Model\Provider\UseCase\Invoice\Create;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\ProviderInvoice\Num;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Shop\Entity\DeleteReason\DeleteReasonRepository;

class Handler
{
    private Flusher $flusher;
    private DeleteReasonRepository $deleteReasonRepository;

    public function __construct(
        DeleteReasonRepository       $deleteReasonRepository,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->deleteReasonRepository = $deleteReasonRepository;
    }

    public function handle(Command $command, Provider $provider): void
    {
        $providerInvoice = new ProviderInvoice(
            $provider,
            implode(',', $command->status_from),
            $command->status_to,
            $command->status_none,
            $this->deleteReasonRepository->get($command->deleteReasonID),
            $command->price,
            $command->price_email,
            $command->email_from,
            $command->priceadd,
            new Num(
                $command->num_number,
                $command->num_number_type,
                $command->num_number_razd,
                $command->num_price,
                $command->num_summ,
                $command->num_quantity,
                $command->num_gtd,
                $command->num_country
            )
        );

        $provider->assignInvoice($providerInvoice);

        $this->flusher->flush();
    }
}
