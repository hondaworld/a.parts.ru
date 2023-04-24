<?php


namespace App\Model\Auto\Entity\MotoModel;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Description
{
    /**
     * @ORM\Column(type="text")
     */
    private $spare;

    /**
     * @ORM\Column(type="text")
     */
    private $acs;

    /**
     * @ORM\Column(type="text")
     */
    private $tuning;

    public function __construct(?string $spare = '', ?string $acs = '', ?string $tuning = '')
    {
        $this->spare = $spare ?: '';
        $this->acs = $acs ?: '';
        $this->tuning = $tuning ?: '';
    }

    public function updateSpare(?string $spare = '')
    {
        $this->spare = $spare ?: '';
    }

    public function updateAcs(?string $acs = '')
    {
        $this->acs = $acs ?: '';
    }

    public function updateTuning(?string $tuning = '')
    {
        $this->tuning = $tuning ?: '';
    }

    public function getSpare(): string
    {
        return $this->spare;
    }

    public function getAcs(): string
    {
        return $this->acs;
    }

    public function getTuning(): string
    {
        return $this->tuning;
    }
}