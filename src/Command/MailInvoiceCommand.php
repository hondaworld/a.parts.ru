<?php

namespace App\Command;

use App\Model\Provider\UseCase\LogInvoice\Create;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoiceRepository;
use App\Service\Email\EmailInvoice;
use App\Service\Email\EmailPrice;
use App\Service\Email\EmailSender;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailInvoiceCommand extends Command
{
    protected static $defaultName = 'app:mail-invoice';
    protected static $defaultDescription = 'Add a short description for your command';

    private Imap $imap;
    private ProviderInvoiceRepository $providerInvoiceRepository;
    private ParameterBagInterface $parameterBag;
    private Create\Handler $handler;
    private EmailSender $emailSender;

    public function __construct(ParameterBagInterface $parameterBag, Imap $imap, ProviderInvoiceRepository $providerInvoiceRepository, Create\Handler $handler, EmailSender $emailSender)
    {
        parent::__construct();
        $this->imap = $imap;
        $this->providerInvoiceRepository = $providerInvoiceRepository;
        $this->parameterBag = $parameterBag;
        $this->handler = $handler;
        $this->emailSender = $emailSender;
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
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');


        $io = new SymfonyStyle($input, $output);

        $emailInvoice = new EmailInvoice($this->imap, $this->providerInvoiceRepository, $this->handler, $this->emailSender);
        $emailInvoice->saveAttachments($this->parameterBag->get('price_directory'));

        $io->success('Файлы загружены');

        return Command::SUCCESS;
    }
}
