<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Document\Entity\Identification\DocumentIdentificationRepository;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class PassportNameOrOrganizationWithPassportTest extends TestCase
{
    public function testPassportNameOrOrganizationWithPassportUrWithOrganization(): void
    {
        $user = (new UserBuilder())->withUr()->build();
        $this->assertEquals($user->getUr()->getOrganizationWithInnAndKpp(), $user->getPassportNameOrOrganizationWithPassport());
    }

    public function testPassportNameOrOrganizationWithPassportUrWithoutOrganization(): void
    {
        $user = (new UserBuilder())->withUr(new Ur('', null, null, null, null, false, true))->build();
        $this->assertNotEquals($user->getUr()->getOrganizationWithInnAndKpp(), $user->getPassportNameOrOrganizationWithPassport());
    }

    public function testPassportNameOrOrganizationWithPassport(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getUserName()->getPassportname(), $user->getPassportNameOrOrganizationWithPassport());
    }

    public function testPassportNameOrOrganizationWithPassportWithDocuments(): void
    {
        $documentIdentificationRepository = $this->createMock(DocumentIdentificationRepository::class);
        $ident = $documentIdentificationRepository->get(DocumentIdentification::PASSPORT_ID);
        $user = (new UserBuilder())->build();
        $user->assignDocument(new Document($user, $ident, '1234', '123456', null, null, null, false));
        $this->assertEquals($user->getUserName()->getPassportname(), $user->getPassportNameOrOrganizationWithPassport());
    }

    public function testPassportNameOrOrganizationWithPassportWithAddPersonName(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals('Частное лицо ' . $user->getUserName()->getPassportname(), $user->getPassportNameOrOrganizationWithPassport(true));
    }
}