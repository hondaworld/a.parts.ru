<?php

namespace App\Command;

use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\Sklad\Service\ExcelHelper;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\User\UserRepository;
use App\Service\Email\EmailSender;
use DomainException;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendPriceCommand extends Command
{
    protected static $defaultName = 'app:send-price';
    protected static $defaultDescription = 'Рассылка прайс-листов';
    private ExcelHelper $excelHelper;
    private UserRepository $userRepository;
    private TemplateRepository $templateRepository;
    private ParameterBagInterface $parameterBag;
    private EmailSender $emailSender;

    public function __construct(
        UserRepository        $userRepository,
        TemplateRepository    $templateRepository,
        ExcelHelper           $excelHelperSummary,
        EmailSender           $emailSender,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
        $this->excelHelper = $excelHelperSummary;
        $this->userRepository = $userRepository;
        $this->templateRepository = $templateRepository;
        $this->parameterBag = $parameterBag;
        $this->emailSender = $emailSender;
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

        $template = $this->templateRepository->get(Template::PRICE_SEND);
        $subject = $template->getSubject();

        $users = $this->userRepository->findForEmailPrices();
        foreach ($users as $user) {
            $fileName = $this->excelHelper->generateFileName($user->getOpt()->getNumber(), $user->getEmailPrice()->getZapSkladID(), false);

            $text = $template->getText([
                'name' => $user->getUserName()->getFirstname(),
                'date' => (new \DateTime())->format('d.m.Y')
            ]);
            $email_send = $user->getUserEmailPrice();
            $file = $this->parameterBag->get('price_directory') . '/email/' . $fileName;

            $attaches = [];
            if (file_exists($file)) {
                $attaches[] = [
                    'body' => file_get_contents($file),
                    'fileName' => $fileName
                ];
//                $email_send = 'info@hondaworld.ru';
                $this->emailSender->sendEmail($email_send, $subject, $text, $attaches, 'price@parts.ru');
//                break;
            }
        }

        $io->success('Прайс разослан');
        return Command::SUCCESS;

    }
}
