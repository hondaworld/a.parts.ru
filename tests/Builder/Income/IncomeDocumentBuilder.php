<?php

namespace App\Tests\Builder\Income;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;

class IncomeDocumentBuilder
{
    private Manager $manager;
    private ?Provider $provider = null;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function withProvider(Provider $provider): self
    {
        $clone = clone $this;
        $clone->provider = $provider;
        return $clone;
    }

    public function build(): IncomeDocument
    {
        $firm = new Firm('Тестовая компания', 'ООО "Тестовая компания"', null, null, null, null, false, true, new Nalog('Налог'), null, null);
        $document_num = 1;
        $document = new Document($document_num, 'pre', 'suf');
        $osn = new Osn('Основание');

        $incomeDocument = new IncomeDocument(
            new DocumentType('Тест', 'Тест', null),
            $document,
            $this->manager,
            $this->provider,
            $this->provider ? $this->provider->getUser() : null,
            null,
            $firm,
            $osn
        );


        return $incomeDocument;
    }
}