<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DeleteArchiveFilesCommand extends Command
{
    protected static $defaultName = 'app:delete-archive-files';
    protected static $defaultDescription = 'Удаление архивных файлов';
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
        $this->parameterBag = $parameterBag;
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

        $dir = $this->parameterBag->get('price_directory') . "/archive/";

        $file_list = scandir($dir);
        foreach ($file_list as $file) {
            if (($file != ".") && ($file != "..") && ($file != ".ftpquota")) {
//                $dateNow = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
                $dateNow = (new \DateTime())->modify('-7 days');
                $date = filemtime($dir . $file);

                if ($date - $dateNow->getTimestamp() < 0) {
                    @unlink($dir . $file);
                }
            }
        }

        $io->success('Архивные файлы удалены');

        return Command::SUCCESS;
    }
}
