<?php


namespace App\Model\Expense\Entity\DocumentPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class FromGruz
{
    /**
     * @ORM\Column(type="string", length=30)
     */
    private $gruz_okpo;

    /**
     * @ORM\Column(type="text")
     */
    private $gruz;


    public function __construct(string $gruz_okpo, string $gruz)
    {
        $this->gruz_okpo = $gruz_okpo;
        $this->gruz = $gruz;
    }

    /**
     * @return string
     */
    public function getGruzOkpo(): string
    {
        return $this->gruz_okpo;
    }

    /**
     * @return string
     */
    public function getGruz(): string
    {
        return $this->gruz;
    }

}