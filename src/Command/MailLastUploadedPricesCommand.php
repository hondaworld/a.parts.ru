<?php

namespace App\Command;

use App\ReadModel\Provider\LogFetcher;
use App\Service\Email\EmailSender;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailLastUploadedPricesCommand extends Command
{
    protected static $defaultName = 'app:mail-last-uploaded-prices';
    protected static $defaultDescription = 'Загруженные прайсы за прошедший день';

    private LogFetcher $logFetcher;
    private EmailSender $emailSender;
    private Environment $twig;

    public function __construct(LogFetcher $logFetcher, EmailSender $emailSender, Environment $twig)
    {
        parent::__construct();
        $this->logFetcher = $logFetcher;
        $this->emailSender = $emailSender;
        $this->twig = $twig;
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
        $io = new SymfonyStyle($input, $output);

        $all = $this->logFetcher->yesterdayUploadedPrices();
        try {
            $text = $this->twig->render('app/providers/prices/upload/logAll.html.twig', ['all' => $all]);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $text = 'Ошибка загрузки шаблона';
        }

        $yesterday = (new DateTime())->modify('-1 day');

        $email_send = "parts@hondaworld.ru, info@hondaworld.ru";
//        $email_send = "info@hondaworld.ru";
        $this->emailSender->sendEmail($email_send, "Загрузка прайсов за " . $yesterday->format('d.m.Y') . "",$text);

        $io->success('Отчет о загруженных прайсах за ' . $yesterday->format('d.m.Y') . ' отправлен');

        return Command::SUCCESS;
    }
}
