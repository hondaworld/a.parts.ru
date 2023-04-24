<?php

namespace App\Tests\Builder\Document;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\User\Entity\User\User;

class UserDocumentBuilder
{
    private bool $isMain;
    private DocumentIdentification $documentIdentification;
    private User $user;

    public function __construct(User $user, bool $isMain = false)
    {
        $this->isMain = $isMain;
        $this->user = $user;
        $this->documentIdentification = new DocumentIdentification('Тестовый тип документа');
    }

    public function build(): Document
    {
        $document = new Document($this->user, $this->documentIdentification, '4501', '123456', 'Паспортный стол', new \DateTime('-10 years'), 'Описание', $this->isMain);
        $this->user->assignDocument($document);

        return $document;
    }
}