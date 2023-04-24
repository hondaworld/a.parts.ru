<?php

namespace App\Command;

use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Service\Email\EmailPrice;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailPriceCommand extends Command
{
    protected static $defaultName = 'app:mail-price';
    protected static $defaultDescription = 'Add a short description for your command';

    private Imap $imap;
    private ProviderPriceRepository $providerPriceRepository;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag, Imap $imap, ProviderPriceRepository $providerPriceRepository)
    {
        parent::__construct();
        $this->imap = $imap;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '4000M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $io = new SymfonyStyle($input, $output);

        $emailPrice = new EmailPrice($this->imap, $this->providerPriceRepository);
        $emailPrice->saveAttachments($this->parameterBag->get('price_directory'));

        $io->success('Файлы загружены');

        return Command::SUCCESS;
    }
}
