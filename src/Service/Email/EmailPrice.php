<?php


namespace App\Service\Email;


use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Service\PriceUploader;
use Doctrine\DBAL\Exception\ConnectionException;
use Exception;
use RarArchive;
use SecIT\ImapBundle\Service\Imap;
use ZipArchive;

class EmailPrice
{
    private $mailbox;
    private $providerPriceRepository;

    public function __construct(Imap $imap, ProviderPriceRepository $providerPriceRepository)
    {
        // TODO поменять e-mail получения прайс-листов
        $this->mailbox = $imap->get('prices_connection');
        $this->providerPriceRepository = $providerPriceRepository;
    }

    public function saveAttachments(string $dir): void
    {

        try {
            $mail_ids = $this->mailbox->searchMailbox('UNSEEN');
        } catch (ConnectionException $ex) {
            die('IMAP connection failed: ' . $ex->getMessage());
        } catch (Exception $ex) {
            die('An error occured: ' . $ex->getMessage());
        }

        foreach ($mail_ids as $mail_id) {

            $email = $this->mailbox->getMail(
                $mail_id, // ID of the email, you want to get
                false // Do NOT mark emails as seen (optional)
            );

            $providerPrices = $this->providerPriceRepository->findWithPriceEmail();
            if (!empty($email->getAttachments())) {
                foreach ($email->getAttachments() as $attachment) {
                    if ($this->getExtension($attachment->name) == 'zip') {
                        $zip = new ZipArchive();
                        $res = $zip->open($attachment->filePath);
                        if ($res === TRUE) {
                            $zip->extractTo($dir . '/auto');
                            $i = 0;
                            while ($filename = $zip->getNameIndex($i)) {
                                $this->saveFile($providerPrices, $dir, null, $filename, $email->fromAddress);
                                $i++;
                            }
                            $zip->close();
                        }
                    } elseif ($this->getExtension($attachment->name) == 'rar') {

                        $res = RarArchive::open($attachment->filePath);
                        $rar_entries = $res->getEntries();
                        if ($rar_entries !== FALSE) {
                            foreach ($rar_entries as $e) {
                                $e->extract($dir . '/auto');
                                $this->saveFile($providerPrices, $dir, null, $e->getName(), $email->fromAddress);
                            }
                            $res->close();
                        }
                    } else {
                        $this->saveFile($providerPrices, $dir, $attachment->filePath, $attachment->name, $email->fromAddress);
                    }

                    @unlink($attachment->filePath);
                }
            }
            $this->mailbox->deleteMail($mail_id);
        }

        $this->mailbox->disconnect();
    }

    public function saveFile(array $providerPrices, string $dir, ?string $filePath, string $filename, string $fromAddress)
    {
        if (count($providerPrices) > 0) {
            foreach ($providerPrices as $providerPrice) {
                if (($pos = strpos(mb_strtolower($filename), mb_strtolower($providerPrice->getPrice()->getPriceEmail()))) !== false) {
                    if (($pos == 0) && (($providerPrice->getPrice()->getEmailFrom() == "") || ($providerPrice->getPrice()->getEmailFrom() == $fromAddress))) {
                        $fileUploader = new PriceUploader($dir . '/auto');
                        $fileUploader->copyFromPath($filePath, $filename);
                        $fileUploader->copyToPath($dir . '/archive/' . $filename);
                        $fileUploader->xlsToCsv($providerPrice);

                        if ($providerPrice->getPrice()->getEmailFrom() == $fromAddress) {
                            break;
                        }
                    }
                }
            }
        }
    }

    private function getExtension(string $filename): string
    {
        return strtolower(substr($filename, strrpos($filename, ".") + 1));
    }
}