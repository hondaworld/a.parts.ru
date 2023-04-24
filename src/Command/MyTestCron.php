<?php


namespace App\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MyTestCron extends Command
{
    private $connection;

    protected static $defaultName = 'app:my:test:cron';

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->connection->createQueryBuilder()->insert('test')
            ->values(
                array(
                    'dateofadded' => 'Now()'
                ))->execute();

        return 0;
    }

}