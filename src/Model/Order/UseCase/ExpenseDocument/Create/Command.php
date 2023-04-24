<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Create;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     */
    public $doc_typeID;

    /**
     * @var bool
     */
    public $isShipping;

    /**
     * @var bool
     */
    public $isService;

    public function __construct(Request $request)
    {
        $this->doc_typeID = $request->query->getInt('doc_typeID');
        $this->isShipping = $request->query->getBoolean('isShipping');
        $this->isService = $request->query->getBoolean('isService');
    }
}
