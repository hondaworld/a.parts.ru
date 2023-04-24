<?php

namespace App\Command;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BalanceLimitCommand extends Command
{
    protected static $defaultName = 'app:balance-limit';
    protected static $defaultDescription = 'Изменение лимита оптовых клиентов';
    private ExpenseDocumentFetcher $expenseDocumentFetcher;
    private UserRepository $userRepository;
    private Flusher $flusher;

    public function __construct(
        ExpenseDocumentFetcher $expenseDocumentFetcher,
        UserRepository         $userRepository,
        Flusher                $flusher
    )
    {
        parent::__construct();
        $this->expenseDocumentFetcher = $expenseDocumentFetcher;
        $this->userRepository = $userRepository;
        $this->flusher = $flusher;
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

        $sales = $this->expenseDocumentFetcher->saleForPeriodGroupOptUser($date, $dateTill);

        $users = $this->userRepository->findByUsers(array_keys($sales));

        foreach ($sales as $userID => $sale) {

            $balanceLimit = floor($sale * 0.3 / 1000) * 1000;

            if ($users[$userID]) {
                $users[$userID]->updateBalanceLimit($balanceLimit);
            }
        }

        $this->flusher->flush();

        $io->success('Лимиты балансов обновлены');
        return Command::SUCCESS;

    }
}
