<?php

namespace App\Command;

use App\Model\Sklad\Service\ExcelHelperSummary;
use App\Model\User\Entity\Opt\OptRepository;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreatePriceSummaryOpt5Command extends Command
{
    protected static $defaultName = 'app:create-price-summary-opt5';
    protected static $defaultDescription = 'Создание excel прайс-листа склейки ОПТ5';
    private ExcelHelperSummary $excelHelperSummary;
    private OptRepository $optRepository;

    public function __construct(
        OptRepository      $userRepository,
        ExcelHelperSummary $excelHelperSummary
    )
    {
        parent::__construct();
        $this->excelHelperSummary = $excelHelperSummary;
        $this->optRepository = $userRepository;
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

        $optID = 6;

        try {
            $opt = $this->optRepository->get($optID);
            $this->excelHelperSummary->save($opt);

            $io->success('Прайс создан');
            return Command::SUCCESS;

        } catch (DomainException | Exception | \PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            $io->error('Ошибка');
            return Command::FAILURE;
        }

    }
}
