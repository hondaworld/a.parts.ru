<?php

namespace App\Model\Firm\UseCase\AllDocuments\Search;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $doc_typeID;

    public $document_num;

    public $year;
}
