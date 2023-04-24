<?php

namespace App\Command;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
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

class SalesPerMonthCommand extends Command
{
    protected static $defaultName = 'app:sales-per-month';
    protected static $defaultDescription = 'Продажи за предыдущий месяц';
    private EmailSender $emailSender;
    private ExpenseDocumentFetcher $expenseDocumentFetcher;
    private ZapCardRepository $zapCardRepository;
    private Environment $twig;

    public function __construct(
        EmailSender            $emailSender,
        ExpenseDocumentFetcher $expenseDocumentFetcher,
        ZapCardRepository      $zapCardRepository,
        Environment            $twig
    )
    {
        parent::__construct();
        $this->emailSender = $emailSender;
        $this->expenseDocumentFetcher = $expenseDocumentFetcher;
        $this->zapCardRepository = $zapCardRepository;
        $this->twig = $twig;
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


        $date = (new DateTime())->modify('-1 month')->modify('first day of this month');
        $dateTill = (clone $date)->modify('+1 month');

        $sales = $this->expenseDocumentFetcher->saleForPeriod($date, $dateTill);

        $zapCards = $this->zapCardRepository->findByZapCards(array_keys($sales));

        uasort($zapCards, function (ZapCard $a, ZapCard $b) use ($sales) {
            if ($sales[$b->getId()] == $sales[$a->getId()])
                return $a->getNumber()->getValue() <=> $b->getNumber()->getValue();
            else
                return $sales[$b->getId()] <=> $sales[$a->getId()];
        });
        try {
            $text = $this->twig->render('app/orders/sales/table.html.twig', ['dateText' => $date->format('m.Y'), 'sales' => $sales, 'zapCards' => $zapCards]);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $text = 'Ошибка загрузки шаблона';
        }

        $email_send = 'parts@hondaworld.ru, sales@parts.ru';
        //$email_send = 'info@hondaworld.ru';
        $this->emailSender->sendEmail($email_send, 'Статистика продаж за ' . $date->format('m.Y'), $text);

        $io->success('Письмо отправлено');
        return Command::SUCCESS;

    }
}
