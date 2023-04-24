<?php

namespace App\Tests\Model\User\User\Document;

use App\Tests\Builder\Document\UserDocumentBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class MainDocumentTest extends TestCase
{
    public function testMainDocument()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainDocument(false);

        $document1 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $this->assertFalse($document1->isMain());
    }

    public function testMainTrueDocument()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainDocument(true);

        $document1 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $this->assertTrue($document1->isMain());
    }

    public function testMainSomeDocuments()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainDocument(false);

        $document1 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $isMain = $user->checkIsMainDocument(false);
        $document2 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $this->assertFalse($document1->isMain());
        $this->assertFalse($document2->isMain());
    }

    public function testMainSomeTrueDocuments()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainDocument(false);

        $document1 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $isMain = $user->checkIsMainDocument(true);
        $document2 = (new UserDocumentBuilder($user, $isMain))->build();
        $user->assignDocument($document1);

        $this->assertFalse($document1->isMain());
        $this->assertTrue($document2->isMain());
    }
}