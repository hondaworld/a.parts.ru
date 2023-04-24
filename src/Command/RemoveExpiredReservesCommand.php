<?php

namespace App\Command;

use App\Model\Order\UseCase\Good\ExpiredReservesDelete;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveExpiredReservesCommand extends Command
{
    protected static $defaultName = 'app:remove-expired-reserves';
    protected static $defaultDescription = 'Удаление просроченных резервов';
    private ExpiredReservesDelete\Handler $handler;

    public function __construct(
        ExpiredReservesDelete\Handler $handler
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
        $io->success('Резервы удалены');

        return Command::SUCCESS;
    }
}
