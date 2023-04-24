<?php

namespace App\Command;

use App\Model\Card\Entity\Main\Main;
use App\Model\Card\Entity\Main\MainRepository;
use App\Model\Flusher;
use App\Service\Sms\SmsRu;
use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SmsBalanceCommand extends Command
{
    protected static $defaultName = 'app:sms-balance';
    protected static $defaultDescription = 'Обновление баланса SMS.RU';
    private MainRepository $mainRepository;
    private Flusher $flusher;

    public function __construct(
        MainRepository         $mainRepository,
        Flusher                $flusher
    )
    {
        parent::__construct();
        $this->mainRepository = $mainRepository;
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

        $main = $this->mainRepository->get(Main::DEFAULT_ID);

        try {
            $smsru = new SmsRu();
            $request = $smsru->getBalance();

            if ($request->status == "OK") { // Запрос выполнен успешно
                $main->updateSmsRuBalance($request->balance);
                $this->flusher->flush();
                $io->success('Баланс SMS.RU обновлен');
            } else {
                $io->error($request->status_text);
            }
        } catch (DomainException $e) {
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
