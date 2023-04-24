<?php


namespace App\Service\Email;


use App\Model\Order\UseCase\Good\ReserveFromMail;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Detail\CreaterFetcher;
use App\Service\Order\OrderUploader;
use Exception;
use SecIT\ImapBundle\Service\Imap;

class EmailOrder
{
    private $mailbox;
    private ReserveFromMail\Handler $handler;
    private EmailSender $emailSender;
    private UserRepository $userRepository;
    private CreaterFetcher $createrFetcher;

    public function __construct(
        Imap                    $imap,
        UserRepository          $userRepository,
        CreaterFetcher          $createrFetcher,
        ReserveFromMail\Handler $handler,
        EmailSender             $emailSender
    )
    {
        $this->mailbox = $imap->get('orders_connection');
        $this->handler = $handler;
        $this->emailSender = $emailSender;
        $this->userRepository = $userRepository;
        $this->createrFetcher = $createrFetcher;
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

            $users = $this->userRepository->findWithPriceEmail();
            if (!empty($email->getAttachments())) {
                foreach ($email->getAttachments() as $attachment) {
                    if (in_array($this->getExtension($attachment->name), ["xls", "xlsx", "csv", "txt"]) && $attachment->name != 'standart.xls') {
                        $this->openFile($users, $dir, $attachment->filePath, $attachment->name, $email->fromAddress, $email->subject);
                    }
                    @unlink($attachment->filePath);
                }
            }
            $this->mailbox->deleteMail($mail_id);
        }

        $this->mailbox->disconnect();
    }

    /**
     * @param User[] $users
     * @param string $dir
     * @param string|null $filePath
     * @param string $fileName
     * @param string $fromAddress
     * @param string $subject
     */
    public function openFile(array $users, string $dir, ?string $filePath, string $fileName, string $fromAddress, string $subject)
    {
        $creaters = $this->createrFetcher->allArray();
        if (count($users) > 0) {
            $isInvoice = false;

            $isStandart = true;

            foreach ($users as $user) {

                if ($user->getPrice()->getEmail() == $fromAddress) {
                    if ($user->getPrice()->getFilename() == "" || (($pos = strpos($fileName, $user->getPrice()->getFilename())) !== false)) {
                        if ($user->getId() == 1) $isStandart = false; // Тест
                        if ($user->getId() == 21422) $isStandart = false; // Exist
                        if ($user->getId() == 22091) $isStandart = false; // Autodoc
                        if ($user->getId() == 22029) $isStandart = false; // Autopiter
                        if ($user->getId() == 18772) $isStandart = false; // РМС Авто
                        if ($user->getId() == 21676) $isStandart = false; // EmEx
                        if ($user->getId() == 26782) $isStandart = false; // Adeo
                        if ($user->getId() == 39118) $isStandart = false; // Adeo
                    }

                    try {
                        $fileUploader = new OrderUploader($dir . '/zakaz');
                        if (in_array($this->getExtension($fileName), ['xls', 'xlsx'])) {
                            $objReader = $fileUploader->openXls($filePath);
                            $arr = $fileUploader->readXls($user, $objReader, $creaters);

                            $command = new ReserveFromMail\Command($arr, $user, $user->getPriceSklad());
                            $arr = $this->handler->handle($command);

                            if ($isStandart) {
                                $standartReader = $fileUploader->openStandartXls();
                                $fileUploader->createRowStandart($standartReader, $arr, 2);
                                $fileUploader->saveXls($standartReader, $fileName, 'Xls');
                            } else {
                                if ($user->getId() == 1) {
                                    $fileUploader->createRowTest($objReader, $user, $arr);
                                } elseif (in_array($user->getId(), [26782, 39118])) {
                                    $fileUploader->createRowAdeo($objReader, $user, $arr);
                                } elseif ($user->getId() == 21676) {
                                    $fileUploader->createRowEmex($objReader, $user, $arr);
                                } elseif ($user->getId() == 18772) {
                                    $fileUploader->createRowRmsAuto($objReader, $user, $arr);
                                } elseif ($user->getId() == 22029) {
                                    $fileUploader->createRowAutoPiter($objReader, $user, $arr);
                                } elseif ($user->getId() == 22091) {
                                    $fileUploader->createRowAutodoc($objReader, $user, $arr);
                                } elseif ($user->getId() == 21422) {
                                    $fileUploader->createRowExist($objReader, $user, $arr);
                                }
                                $fileUploader->saveXls($objReader, $fileName, $fileUploader->getFileType($filePath));
                            }

                        } else {
                            $arr = $fileUploader->readCsv($user, $filePath, $creaters);

                            $command = new ReserveFromMail\Command($arr, $user, $user->getPriceSklad());
                            $arr = $this->handler->handle($command);

                            $standartReader = $fileUploader->openStandartXls();
                            $fileUploader->createRowStandart($standartReader, $arr, 2);
                            $fileUploader->saveXls($standartReader, $this->getFileNameWithoutExtension($fileName) . '.xls', 'Xls');
                        }

                        $this->emailSender->sendEmail(
                            ($user->getPrice()->getEmailSend() != '' ? $user->getPrice()->getEmailSend() : $user->getPrice()->getEmail()),
                            'Re: ' . $subject,
                            '',
                            [[
                                'body' => $fileUploader->getBodyFileName(),
                                'fileName' => $fileUploader->getFileName()
                            ]]
                        );
                        $fileUploader->delete();
                    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
                    }

                }
            }
//            if (!$isInvoice) {
//                $this->emailBadFile($filename, $fromAddress);
//            }
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

    private function getExtension(string $filename): string
    {
        return strtolower(substr($filename, strrpos($filename, ".") + 1));
    }

    private function getFileNameWithoutExtension(string $filename): string
    {
        return substr($filename, 0, strrpos($filename, "."));
    }
}