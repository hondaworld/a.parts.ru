<?php

namespace App\Service;

use App\Model\Flusher;
use DomainException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EmailUploader
{
    protected string $targetDirectory;
    protected string $fileName;
    protected Flusher $flusher;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
//        $this->fileName = $fileName;
    }

    public function upload(UploadedFile $file)
    {
        $this->fileName = $file->getClientOriginalName();

        try {
            $file->move($this->targetDirectory(), $this->fileName);
        } catch (FileException $e) {
            throw new DomainException($e);
        }
    }

    public function uploadAndCopy(UploadedFile $file)
    {
        $this->fileName = $file->getClientOriginalName();

        try {
            copy($file->getPathname(), $this->getFullFileName());
        } catch (FileException $e) {
            throw new DomainException($e);
        }
    }

    public function copyFromPath(?string $source, string $filename): void
    {
        $this->fileName = $filename;
        if ($source) {
            try {
                copy($source, $this->getFullFileName());
            } catch (FileException $e) {
                throw new DomainException($e);
            }
        }
    }

    public function copy(string $copyName)
    {
        try {
            copy($this->getFullFileName(), $this->targetDirectory() . '/' . $copyName);
        } catch (FileException $e) {
            throw new DomainException($e);
        }
    }

    public function copyToPath(string $copyPath)
    {
        try {
            copy($this->getFullFileName(), $copyPath);
        } catch (FileException $e) {
            throw new DomainException($e);
        }
    }


    public function delete()
    {
        @unlink($this->getFullFileName());
    }

    public function getCsvLine($data, string $razd)
    {
        return fgetcsv($data, 4096, $razd, '"', '"');
    }

    public function targetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function getFullFileName(): string
    {
        return $this->targetDirectory() . '/' . $this->fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    protected function iconv_text($str): string
    {
        return mb_convert_encoding($str, "UTF-8", "Windows-1251");
    }

    protected function is_utf($str): bool
    {
        if (mb_convert_encoding($str, "UTF-8", "UTF-8") == $str)
            return true;
        else
            return false;
    }

    protected function getExtension(string $filename): string
    {
        return strtolower(substr($filename, strrpos($filename, ".") + 1));
    }

    public function getBodyFileName(): string
    {
        return file_get_contents($this->getFullFileName());
    }
}