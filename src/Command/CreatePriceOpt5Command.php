<?php

namespace App\Command;

use App\Model\Sklad\Service\ExcelHelper;
use App\Model\User\Entity\Opt\OptRepository;
use DomainException;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreatePriceOpt5Command extends Command
{
    protected static $defaultName = 'app:create-price-opt5';
    protected static $defaultDescription = 'Создание excel прайс-листа ОПТ5 для всех складов';
    private ExcelHelper $excelHelper;
    private OptRepository $optRepository;

    public function __construct(
        OptRepository      $userRepository,
        ExcelHelper        $excelHelperSummary
    )
    {
        parent::__construct();
        $this->excelHelper = $excelHelperSummary;
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
        $isSimple = false;

        try {
            $opt = $this->optRepository->get($optID);
            $this->excelHelper->save($opt, null, $isSimple);

            $io->success('Прайс создан');
            return Command::SUCCESS;

        } catch (DomainException | Exception $e) {
            $io->error('Ошибка');
            return Command::FAILURE;
        }

    }
}
