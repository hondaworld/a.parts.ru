<?php

namespace App\Model\Expense\UseCase\Shipping\Search;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $document_num;

    public $year;
}
