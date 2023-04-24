<?php

namespace App\Model\Work\UseCase\Group\Create;

use App\Model\Work\Entity\Category\WorkCategory;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $norma;

    public $isTO;

    public $sort;

    public $workCategory;

    public function __construct(WorkCategory $workCategory)
    {
        $this->workCategory = $workCategory;
    }
}
