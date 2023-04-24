<?php

namespace App\Model\Order\UseCase\Check\Pay;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $expenseDocumentID;

    public $managerID;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, местоположение кассы"
     * )
     */
    public $zapSkladID;

    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Укажите, пожалуйста, сумму чека"
     * )
     */
    public $sum;

    public function __construct(ExpenseDocument $expenseDocument, int $managerID)
    {
        $this->managerID = $managerID;
        $this->expenseDocumentID = $expenseDocument->getId();
        $this->sum = $expenseDocument->getGoodsSum();
    }
}
