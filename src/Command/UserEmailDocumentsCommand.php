<?php

namespace App\Command;

use App\Controller\PrintDocuments\PrintExpenseDocumentsController;
use App\Controller\PrintDocuments\PrintSchetFakController;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\ExpenseDocumentXlsHelper;
use App\Model\Expense\Service\SchetFakPrintService;
use App\Model\Expense\Service\SchetFakXlsHelper;
use App\Model\Flusher;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Service\Converter\NumberInWordsConverter;
use App\Service\Email\EmailSender;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserEmailDocumentsCommand extends Command
{
    protected static $defaultName = 'app:user-email-documents';
    protected static $defaultDescription = 'Документы клиентов';
    private EmailSender $emailSender;
    private ExpenseDocumentRepository $expenseDocumentRepository;
    private ExpenseDocumentPrintService $expenseDocumentPrintService;
    private PrintExpenseDocumentsController $printExpenseDocumentsController;
    private ExpenseDocumentXlsHelper $expenseDocumentXlsHelper;
    private NumberInWordsConverter $numberInWordsConverter;
    private ParameterBagInterface $parameterBag;
    private PrintSchetFakController $printSchetFakController;
    private SchetFakXlsHelper $schetFakXlsHelper;
    private SchetFakPrintService $schetFakPrintService;
    private TemplateRepository $templateRepository;
    private Flusher $flusher;

    public function __construct(
        EmailSender                     $emailSender,
        ExpenseDocumentRepository       $expenseDocumentRepository,
        ExpenseDocumentPrintService     $expenseDocumentPrintService,
        PrintExpenseDocumentsController $printExpenseDocumentsController,
        PrintSchetFakController         $printSchetFakController,
        ExpenseDocumentXlsHelper        $expenseDocumentXlsHelper,
        SchetFakXlsHelper               $schetFakXlsHelper,
        NumberInWordsConverter          $numberInWordsConverter,
        SchetFakPrintService            $schetFakPrintService,
        TemplateRepository              $templateRepository,
        ParameterBagInterface           $parameterBag,
        Flusher                         $flusher
    )
    {
        parent::__construct();
        $this->emailSender = $emailSender;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->expenseDocumentPrintService = $expenseDocumentPrintService;
        $this->printExpenseDocumentsController = $printExpenseDocumentsController;
        $this->expenseDocumentXlsHelper = $expenseDocumentXlsHelper;
        $this->numberInWordsConverter = $numberInWordsConverter;
        $this->parameterBag = $parameterBag;
        $this->printSchetFakController = $printSchetFakController;
        $this->schetFakXlsHelper = $schetFakXlsHelper;
        $this->schetFakPrintService = $schetFakPrintService;
        $this->templateRepository = $templateRepository;
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

        $expenseDocument = $this->expenseDocumentRepository->getNextUserDocument();

        if ($expenseDocument) {
            $user = $expenseDocument->getUser();

            $template = $this->templateRepository->get(Template::USER_DOCUMENTS);
            $subject = $template->getSubject();
            $subject = str_replace("{document_num}", $expenseDocument->getDocument()->getNum(), $subject);

            $text = $template->getText([
                'name' => $user->getUserName()->getFirstname()
            ]);

            $attaches = [];
            $expenseDocumentPrint = $this->expenseDocumentPrintService->getNakladnaya($expenseDocument);
            try {

                $writer = $this->printExpenseDocumentsController->excel($this->expenseDocumentXlsHelper, $this->numberInWordsConverter, $expenseDocument, $expenseDocumentPrint);
                $fileName = $this->parameterBag->get('user_documents_directory') . "/nakladnaya" . $expenseDocument->getId() . ".xls";
                $writer->save($fileName);

                $attaches[] = [
                    'tmp_name' => $fileName,
                    'body' => file_get_contents($fileName),
                    'fileName' => "Накладная №" . $expenseDocument->getDocument()->getNum() . " от " . $expenseDocument->getDateofadded()->format('d.m.Y') . ".xls"
                ];
            } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
            }
            try {
                $schetFak = $expenseDocument->getSchetFak();
                if ($schetFak) {
                    $schetFakPrint = $this->schetFakPrintService->getSchetFak($schetFak);

                    $writer = $this->printSchetFakController->excel($expenseDocument, $schetFak, $schetFakPrint, $this->schetFakXlsHelper);
                    $fileName = $this->parameterBag->get('user_documents_directory') . "/schet_fak" . $schetFak->getId() . ".xls";
                    $writer->save($fileName);

                    $attaches[] = [
                        'tmp_name' => $fileName,
                        'body' => file_get_contents($fileName),
                        'fileName' => "Счет фактура №" . $schetFak->getDocument()->getNum() . " от " . $schetFak->getDateofadded()->format('d.m.Y') . ".xls"
                    ];
                }
            } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
            }

            $email_send = 'buh@hondamail.ru, parts@hondamail.ru';
            //$email_send = 'info@hondaworld.ru';
            $this->emailSender->sendEmail($email_send, 'Отгрузка ' . $expenseDocument->getDateofadded()->format('d/m/Y'), '', $attaches);

            if (($userEmail = $user->getEmail()->getValueWithCheck()) && $user->isAllowSendEmailWithDocuments()) {

                //$userEmail = 'info@hondaworld.ru';
                $this->emailSender->sendEmail($userEmail, $subject, $text, $attaches);
            }

            foreach ($attaches as $attach) {
                @unlink($attach['tmp_name']);
            }

            $expenseDocument->documentsSent();
            $this->flusher->flush();
            $io->success('Письмо отправлено');
        } else {
            $io->success('Свободных накладных нет');
        }

        return Command::SUCCESS;

    }
}
