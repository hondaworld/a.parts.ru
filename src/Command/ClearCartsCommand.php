<?php

namespace App\Command;

use App\Model\Flusher;
use App\Model\Order\Entity\Order\OrderRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearCartsCommand extends Command
{
    protected static $defaultName = 'app:clear-carts';
    protected static $defaultDescription = 'Удаление корзин';
    private OrderRepository $orderRepository;
    private Flusher $flusher;

    public function __construct(
        OrderRepository $orderRepository,
        Flusher         $flusher
    )
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->flusher = $flusher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->orderRepository->removeNotConfirmedOrders();
        $this->flusher->flush();

        $io->success('Удалено ' . $count . ' заказов');

        return Command::SUCCESS;
    }
}
