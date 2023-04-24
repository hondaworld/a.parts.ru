<?php

namespace App\Command;

use App\Model\Provider\Entity\LogPrice\LogPriceRepository;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAllRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeletePriceLogsCommand extends Command
{
    protected static $defaultName = 'app:delete-price-logs';
    protected static $defaultDescription = 'Удаление логов прайсов';
    private LogPriceRepository $logPriceRepository;
    private LogPriceAllRepository $logPriceAllRepository;

    public function __construct(
        LogPriceRepository $logPriceRepository,
        LogPriceAllRepository $logPriceAllRepository
    )
    {
        parent::__construct();
        $this->logPriceRepository = $logPriceRepository;
        $this->logPriceAllRepository = $logPriceAllRepository;
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

        $this->logPriceRepository->removeOld();
        $this->logPriceAllRepository->removeOld();
        $io->success('Логи удалены');

        return Command::SUCCESS;
    }
}
