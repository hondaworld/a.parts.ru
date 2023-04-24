<?php


namespace App\Service\Email;


use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Provider\UseCase\LogInvoice\Create;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoiceRepository;
use App\Service\Invoice\InvoiceUploader;
use Exception;
use SecIT\ImapBundle\Service\Imap;

class EmailInvoice
{
    private $mailbox;
    private ProviderInvoiceRepository $providerInvoiceRepository;
    private Create\Handler $handler;
    private EmailSender $emailSender;

    public function __construct(
        Imap                      $imap,
        ProviderInvoiceRepository $providerInvoiceRepository,
        Create\Handler            $handler,
        EmailSender               $emailSender
    )
    {
        // TODO поменять e-mail получения инвойсов
        $this->mailbox = $imap->get('invoices_connection');
        $this->providerInvoiceRepository = $providerInvoiceRepository;
        $this->handler = $handler;
        $this->emailSender = $emailSender;
    }

    public function saveAttachments(string $dir): void
    {

        try {
            $mail_ids = $this->mailbox->searchMailbox('UNSEEN');
        } catch (Exception $ex) {
            die('An error occured: ' . $ex->getMessage());
        }

        foreach ($mail_ids as $mail_id) {

            $email = $this->mailbox->getMail(
                $mail_id, // ID of the email, you want to get
                false // Do NOT mark emails as seen (optional)
            );

            $providerInvoices = $this->providerInvoiceRepository->findWithPriceEmail();
            if (!empty($email->getAttachments())) {
                foreach ($email->getAttachments() as $attachment) {
                    $this->saveFile($providerInvoices, $dir, $attachment->filePath, $attachment->name, $email->fromAddress);
                    @unlink($attachment->filePath);
                }
            }
            $this->mailbox->deleteMail($mail_id);
        }

        $this->mailbox->disconnect();
    }

    /**
     * @param ProviderInvoice[] $providerInvoices
     * @param string $dir
     * @param string|null $filePath
     * @param string $filename
     * @param string $fromAddress
     */
    public function saveFile(array $providerInvoices, string $dir, ?string $filePath, string $filename, string $fromAddress)
    {
        if (count($providerInvoices) > 0) {
            $isInvoice = false;
            foreach ($providerInvoices as $providerInvoice) {
                if ($providerInvoice->getEmailFrom() == $fromAddress &&
                    (
                        $providerInvoice->getPriceEmail() == "" ||
                        (($pos = strpos(mb_strtolower($filename), mb_strtolower($providerInvoice->getPriceEmail()))) !== false) &&
                        $pos == 0
                    )
                ) {
                    $fileUploader = new InvoiceUploader($dir . '/invoice');
                    $fileUploader->copyFromPath($filePath, $filename);
                    $fileUploader->xlsToCsv($providerInvoice);

                    $invoices = $fileUploader->uploadPrice($providerInvoice);
                    $this->handler->handle($providerInvoice, $invoices);
                    $isInvoice = true;
                }
            }
            if (!$isInvoice) {
                $this->emailBadFile($filename, $fromAddress);
            }
        }
    }

    private function emailBadFile(string $filename, string $fromAddress)
    {
        $text = "Не распознан прайс-лист<br><br>";
        $text .= "E-mail отправителя: " . $fromAddress . "<br>";
        $text .= "Файл: " . $filename . "<br>";
        $userEmail = "info@hondaworld.ru, parts@hondaworld.ru";
//        $userEmail = "info@hondaworld.ru";
        $this->emailSender->sendEmail($userEmail, "Отработка скрипта Invoice", $text);
    }
}