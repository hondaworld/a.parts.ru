<?php

namespace App\Tests\Model\User\User\Documents;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UserDocumentTest extends TestCase
{
    public function testUserDocumentCreate(): void
    {
        $user = (new UserBuilder())->build();
        $document = new Document($user, new DocumentIdentification('Тест'), '1501', '154215', null, null, null, false);
        $user->assignDocument($document);
        $this->assertCount(1, $user->getDocuments());
    }
}