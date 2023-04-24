<?php

namespace App\Command;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\LogPrice\LogPriceRepository;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAllRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Service\PriceUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadPriceCommand extends Command
{
    private $connection;

    protected static $defaultName = 'app:upload-price';
    private $providerPriceRepository;
    private $providerPriceFetcher;
    private $parameterBag;
    private $flusher;
    private $createrFetcher;
    private $createrRepository;
    private $priceUploaderFetcher;
    private $logPriceRepository;
    private $logPriceAllRepository;

    public function __construct(
        EntityManagerInterface  $em,
        ProviderPriceRepository $logFetcher,
        ProviderPriceFetcher    $providerPriceFetcher,
        ParameterBagInterface   $parameterBag,
        Flusher                 $flusher,
        CreaterFetcher          $createrFetcher,
        CreaterRepository       $createrRepository,
        PriceUploaderFetcher    $priceUploaderFetcher,
        LogPriceRepository      $logPriceRepository,
        LogPriceAllRepository   $logPriceAllRepository
    )
    {
        $this->connection = $em->getConnection();
        parent::__construct();

        $this->parameterBag = $parameterBag;
        $this->providerPriceRepository = $logFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->flusher = $flusher;
        $this->createrFetcher = $createrFetcher;
        $this->createrRepository = $createrRepository;
        $this->priceUploaderFetcher = $priceUploaderFetcher;
        $this->logPriceRepository = $logPriceRepository;
        $this->logPriceAllRepository = $logPriceAllRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $io = new SymfonyStyle($input, $output);

        $prices = $this->providerPriceFetcher->findUploadedId($this->parameterBag->get('price_directory') . '/auto');

        $this->priceUploaderFetcher->uploadingPriceDelete();

        if ($prices) {
            try {
                $providerPrice = $this->providerPriceRepository->get($prices[0]);
                $fileUploader = new PriceUploader($this->parameterBag->get('price_directory') . '/auto');
                if ($fileUploader->setFileNameFromProviderPrice($providerPrice)) {
                    $fileUploader->uploadPriceAndDelete(
                        $providerPrice,
                        $this->createrFetcher,
                        $this->createrRepository,
                        $this->priceUploaderFetcher,
                        $this->logPriceRepository,
                        $this->logPriceAllRepository,
                        $this->flusher
                    );
                }
                $io->success($providerPrice->getDescription() . ' загружен');
            } catch (\DomainException $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
