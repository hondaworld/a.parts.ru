<?php

namespace App\Command;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;
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

class ReviewSendCommand extends Command
{
    protected static $defaultName = 'app:review-send';
    protected static $defaultDescription = 'Отзывы клиентов';
    private EmailSender $emailSender;
    private Environment $twig;
    private UserRepository $userRepository;
    private Flusher $flusher;

    public function __construct(
        EmailSender            $emailSender,
        UserRepository         $userRepository,
        Flusher                $flusher,
        Environment            $twig
    )
    {
        parent::__construct();
        $this->emailSender = $emailSender;
        $this->twig = $twig;
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


        $date = (new DateTime())->modify('-1 day');
        $dateTill = (clone $date)->modify('+1 day');

        $users = $this->userRepository->findUsersNotReview($date, $dateTill);

        $usersTemplate = $users;

        foreach ($usersTemplate as $k => $user) {
            if (!$user->getMainContact()->getTown()->isMsk() && !$user->getMainContact()->getTown()->isSpb()) {
                unset($usersTemplate[$k]);
            }
        }

        try {
            $text = $this->twig->render('app/users/review/mail/table.html.twig', ['dateText' => $date->format('d.m.Y'), 'users' => $usersTemplate]);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            $text = 'Ошибка загрузки шаблона';
        }

        $email_send = 'parts@hondaworld.ru';
        //$email_send = 'info@hondaworld.ru';
        $this->emailSender->sendEmail($email_send, 'Продажи за ' . $date->format('d.m.Y'), $text);


        foreach ($users as $user) {
            $user->getReview()->reviewSent();
        }
        $this->flusher->flush();

        $io->success('Письмо отправлено');
        return Command::SUCCESS;

    }
}
