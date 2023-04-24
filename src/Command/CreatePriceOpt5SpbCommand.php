<?php

namespace App\Command;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
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

class CreatePriceOpt5SpbCommand extends Command
{
    protected static $defaultName = 'app:create-price-opt5-spb';
    protected static $defaultDescription = 'Создание excel прайс-листа ОПТ5 для СПБ';
    private ExcelHelper $excelHelper;
    private ZapSkladRepository $zapSkladRepository;
    private OptRepository $optRepository;

    public function __construct(
        OptRepository      $userRepository,
        ExcelHelper        $excelHelperSummary,
        ZapSkladRepository $zapSkladRepository
    )
    {
        parent::__construct();
        $this->excelHelper = $excelHelperSummary;
        $this->zapSkladRepository = $zapSkladRepository;
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
        $zapSkladID = ZapSklad::SPB;
        $isSimple = false;

        try {
            $opt = $this->optRepository->get($optID);
            $zapSklad = $this->zapSkladRepository->get($zapSkladID);
            $this->excelHelper->save($opt, $zapSklad, $isSimple);

            $io->success('Прайс создан');
            return Command::SUCCESS;

        } catch (DomainException | Exception $e) {
            $io->error('Ошибка');
            return Command::FAILURE;
        }

    }
}
