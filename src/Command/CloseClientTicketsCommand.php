<?php

namespace App\Command;

use App\Model\Ticket\UseCase\ClientTicket\Close;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CloseClientTicketsCommand extends Command
{
    protected static $defaultName = 'app:close-client-tickets';
    protected static $defaultDescription = 'Закрытие тикетов';
    private Close\Handler $handler;

    public function __construct(
        Close\Handler $handler
    )
    {
        parent::__construct();
        $this->handler = $handler;
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

        $this->handler->handle();
        $io->success('Тикеты закрыты');

        return Command::SUCCESS;
    }
}
