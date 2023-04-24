<?php

namespace App\Command;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\Shop\DeleteReasonFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
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

class SendIncomeStatusChangeCommand extends Command
{
    protected static $defaultName = 'app:send-income-status-change';
    protected static $defaultDescription = 'Изменение статусов приходов';
    private EmailSender $emailSender;
    private Environment $twig;
    private UserRepository $userRepository;
    private Flusher $flusher;
    private OrderGoodFetcher $orderGoodFetcher;
    private ZapCardRepository $zapCardRepository;
    private ZapSkladFetcher $zapSkladFetcher;
    private DeleteReasonFetcher $deleteReasonFetcher;
    private IncomeStatusFetcher $incomeStatusFetcher;
    private TemplateRepository $templateRepository;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        EmailSender         $emailSender,
        UserRepository      $userRepository,
        OrderGoodFetcher    $orderGoodFetcher,
        ZapCardRepository   $zapCardRepository,
        ZapSkladFetcher     $zapSkladFetcher,
        DeleteReasonFetcher $deleteReasonFetcher,
        IncomeStatusFetcher $incomeStatusFetcher,
        TemplateRepository  $templateRepository,
        OrderGoodRepository $orderGoodRepository,
        Flusher             $flusher,
        Environment         $twig
    )
    {
        parent::__construct();
        $this->emailSender = $emailSender;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->flusher = $flusher;
        $this->orderGoodFetcher = $orderGoodFetcher;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->deleteReasonFetcher = $deleteReasonFetcher;
        $this->incomeStatusFetcher = $incomeStatusFetcher;
        $this->templateRepository = $templateRepository;
        $this->orderGoodRepository = $orderGoodRepository;
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

        $template = $this->templateRepository->get(Template::INCOME_STATUSES);
        $subject = $template->getSubject();

        $incomeStatuses = $this->incomeStatusFetcher->assoc();
        $deleteReasons = $this->deleteReasonFetcher->assoc();
        $sklads = $this->zapSkladFetcher->allSklads();

        $goods = $this->orderGoodFetcher->findIncomeStatusChanged();
        $users = $this->userRepository->findByUsers(array_keys($goods));

        foreach ($goods as $userID => &$orderGoods) {

            foreach ($orderGoods as $k => &$orderGood) {

                if (in_array($orderGood['lastIncomeStatus'], IncomeStatus::ARR_NOT_SEND)) {
                    unset($orderGoods[$k]);
                } else {
                    $good = $this->orderGoodRepository->get($orderGood['goodID']);

                    $zapCard = $this->zapCardRepository->getByNumberAndCreaterID($orderGood['number'], $orderGood['createrID']);
                    if ($zapCard) {
                        $orderGood['detail_name'] = $zapCard->getDetailName();
                    }
                    $orderGood['discountPrice'] = round($orderGood['price'] - $orderGood['price'] * $orderGood['discount'] / 100);

                    if ($orderGood['lastIncomeStatusEmailed'] == 0) {
                        $orderGood['status'] = $incomeStatuses[$orderGood['lastIncomeStatus']];
                        $status_reason_add = "";
                        switch ($orderGood['lastIncomeStatus']) {
                            case IncomeStatus::IN_PATH:
                                $status_reason_add = "на склад ";
                                break;
                            case IncomeStatus::INCOME_IN_WAREHOUSE:
                                $status_reason_add = "склад ";
                                break;
                        }
                        if ($orderGood['zapSkladID'])
                            $status_reason = $sklads[$orderGood['zapSkladID']]['name'];
                        else {
                            $status_reason = $orderGood['incomeSkladID'] ? $sklads[$orderGood['incomeSkladID']]['name'] : '';
                        }

                        if ($orderGood['lastIncomeStatus'] == IncomeStatus::PURCHASED)
                            $orderGood['status_reason'] = "";
                        else
                            $orderGood['status_reason'] = $status_reason_add . $status_reason;

                        $orderGood['dateofstatus'] = $orderGood['lastIncomeStatusDate'];
                        $orderGood['class'] = 'text-secondary';

                        $good->getLastIncomeStatusData()->emailed();
                    } else if ($orderGood['deleteReasonEmailed'] == 0) {
                        $orderGood['status'] = "Отказ";
                        $orderGood['status_reason'] = $orderGood['deleteReasonID'] ? $deleteReasons[$orderGood['deleteReasonID']] : '';
                        $orderGood['dateofstatus'] = $orderGood['dateofdeleted'];
                        $orderGood['class'] = 'text-danger';

                        $good->deleteReasonWasEmailed();
                    }
                }
            }

            $user = $users[$userID];
            if (($email_send = $user->getEmail()->getValueWithCheck()) && $user->isAllowSendEmailWithIncomeStatuses() && count($orderGoods) > 0) {

                try {
                    $table = $this->twig->render('app/orders/goods/incomeStatusesChange/table.html.twig', ['orderGoods' => $orderGoods]);
                } catch (LoaderError | RuntimeError | SyntaxError $e) {
                    $table = 'Ошибка загрузки шаблона';
                }

                $text = $template->getText([
                    'name' => $user->getUserName()->getFirstname(),
                    'date' => (new DateTime())->format('d.m.Y'),
                    'table' => $table
                ]);

                dump($email_send);
                try {
                    $this->emailSender->sendEmail($email_send, $subject, $text);
                } catch (\DomainException $e) {
                    $io->error($e->getMessage());
                }
            }

            $this->flusher->flush();
        }

        $io->success('Письма отправлены');
        return Command::SUCCESS;
    }
}
