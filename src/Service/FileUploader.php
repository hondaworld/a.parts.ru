<?php


namespace App\Service;


use DomainException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private const ADMIN_UPLOAD_URL = 'http://admin.parts.ru/api/uploadFiles.php';
    private const ADMIN_DELETE_URL = 'http://admin.parts.ru/api/deleteFiles.php';
    private string $upload_hash;
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->upload_hash = md5('gfdgdsg334' . date('Ymd') . 'ertgfc4g3');
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, bool $isMkDir = false): string
    {
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        try {
            if ($isMkDir) {
                if (!file_exists($this->targetDirectory())) {
                    mkdir($this->targetDirectory(), 0777, true);
                }
            }
            $file->move($this->targetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new DomainException($e);
        }

        return $fileName;
    }

    public function uploadToAdminAndDelete(UploadedFile $file, string $deleteFile = '', array $newSize = [])
    {
        $fileName = $this->uploadToAdmin($file, $deleteFile, $newSize);
        $this->deleteFromAdmin($file);
        return $fileName;
    }

    public function uploadToAdmin(UploadedFile $file, string $deleteFile = '', array $newSize = [])
    {
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        $post = http_build_query(
            array(
                'hash' => $this->upload_hash,
                'targetDirectory' => $this->targetDirectory(),
                'name' => $fileName,
                'deleteFile' => $deleteFile,
                'file' => file_get_contents($file->getPathname()),
                'newSize' => $newSize
            )
        );

        $options = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post,
                ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        );

        $context = stream_context_create($options);
        $answer = file_get_contents(self::ADMIN_UPLOAD_URL, false, $context);

        if ($answer == 'OK') {
//            @unlink($file->getPathname());
            return $fileName;
        }

        return false;
    }

    public function deleteUploadedFile(UploadedFile $file)
    {
        @unlink($file->getPathname());
    }

    public function deleteFromAdmin(string $deleteFile): bool
    {
        $post = http_build_query(
            array(
                'hash' => $this->upload_hash,
                'targetDirectory' => $this->targetDirectory(),
                'deleteFile' => $deleteFile
            )
        );

        $options = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post,
                ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        );

        $context = stream_context_create($options);
        $answer = file_get_contents(self::ADMIN_DELETE_URL, false, $context);

        if ($answer == 'OK') {
            return true;
        }
        return false;
    }

    public function resize($fileName, int $maxWidth = 0, int $maxHeight = 0)
    {
        if ($maxWidth > 0 && $maxHeight > 0) {
            (new ImageOptimizer())->resize($this->targetDirectory() . '/' . $fileName, $maxWidth, $maxHeight);
        }
    }

    public function orientation($fileName)
    {
        (new ImageOptimizer())->orientation($this->targetDirectory() . '/' . $fileName);
    }

    public function delete($existFile)
    {
        @unlink($this->targetDirectory() . '/' . $existFile);
    }

    public function targetDirectory(): string
    {
        return $this->targetDirectory;
    }
}