<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Test;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     */
    public $document_num;

    /**
     * @var \DateTime
     */
    public $document_date;
}
