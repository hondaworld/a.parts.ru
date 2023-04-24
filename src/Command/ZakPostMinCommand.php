<?php

namespace App\Command;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Main\Main;
use App\Model\Card\Entity\Main\MainRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Sklad\ZapSkladLocationFetcher;
use App\Service\Price\PartPriceService;
use App\Service\Sms\SmsRu;
use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ZakPostMinCommand extends Command
{
    protected static $defaultName = 'app:zak-post-min';
    protected static $defaultDescription = 'Добавление приходов для деталей с количеством меньше минимума';
    private MainRepository $mainRepository;
    private Flusher $flusher;
    private ZapSkladLocationFetcher $zapSkladLocationFetcher;
    private IncomeFetcher $incomeFetcher;
    private IncomeRepository $incomeRepository;
    private ZapCardRepository $zapCardRepository;
    private PartPriceService $partPriceService;
    private ProviderPriceRepository $providerPriceRepository;
    private IncomeStatusRepository $incomeStatusRepository;

    public function __construct(
        MainRepository          $mainRepository,
        ZapSkladLocationFetcher $zapSkladLocationFetcher,
        IncomeFetcher           $incomeFetcher,
        IncomeRepository        $incomeRepository,
        ZapCardRepository       $zapCardRepository,
        PartPriceService        $partPriceService,
        ProviderPriceRepository $providerPriceRepository,
        IncomeStatusRepository  $incomeStatusRepository,
        Flusher                 $flusher
    )
    {
        parent::__construct();
        $this->mainRepository = $mainRepository;
        $this->flusher = $flusher;
        $this->zapSkladLocationFetcher = $zapSkladLocationFetcher;
        $this->incomeFetcher = $incomeFetcher;
        $this->incomeRepository = $incomeRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->partPriceService = $partPriceService;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
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

        $zapCards = $this->zapSkladLocationFetcher->findPositiveQuantityMin();

//        $quantityMin = $zapCards[360900];
//        $zapCards = [
//            360900 => $quantityMin
//        ];

        $quantities = $this->incomeFetcher->getQuantityFormZakPostMinByZapCards(array_keys($zapCards));

        $zapCardModels = $this->zapCardRepository->findByZapCards(array_keys($zapCards));

        $i = 0;
        foreach ($zapCards as $zapCardID => $quantityMin) {
            if ($quantities[$zapCardID] >= 0 && $quantityMin > $quantities[$zapCardID]) {
                $zapCard = $zapCardModels[$zapCardID];
                $income = $this->incomeRepository->getNotOrderedByZapCard($zapCard);
                if ($income) {
                    $income->increaseOrderQuantity($quantityMin - $quantities[$zapCardID]);
                } else {
                    $optimal = $this->partPriceService->getOptimalProviderPrice($zapCard);
                    if ($optimal) {
                        $providerPrice = $this->providerPriceRepository->get($optimal['providerPriceID']);
                        $incomeNew = new Income(
                            $providerPrice,
                            $this->incomeStatusRepository->get(IncomeStatus::DEFAULT_STATUS),
                            $zapCard,
                            $quantityMin - $quantities[$zapCardID],
                            $optimal['priceZak'],
                            $optimal['priceDostUsd'],
                            $optimal['priceWithDostRub']
                        );
                        $this->incomeRepository->add($incomeNew);
                    }
                }
                $i++;
            }
        }
        $this->flusher->flush();

        $io->success('Обработано ' . $i . ' записей');

        return Command::SUCCESS;
    }
}
