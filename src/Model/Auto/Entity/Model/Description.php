<?php


namespace App\Model\Auto\Entity\Model;


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

    /**
     * @ORM\Column(type="text")
     */
    private $service;

    public function __construct(?string $spare = '', ?string $acs = '', ?string $tuning = '', ?string $service = '')
    {
        $this->spare = $spare ?: '';
        $this->acs = $acs ?: '';
        $this->tuning = $tuning ?: '';
        $this->service = $service ?: '';
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

    public function updateService(?string $service = '')
    {
        $this->service = $service ?: '';
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

    public function getService(): string
    {
        return $this->service;
    }
}