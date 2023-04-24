<?php


namespace App\Model\Manager\Entity\Manager;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Name
{
    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $firstname;
    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $lastname;
    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $middlename;

    public function __construct(string $firstname, string $lastname, string $middlename)
    {
        Assert::notEmpty($firstname);
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->middlename = $middlename;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getMiddlename(): string
    {
        return $this->middlename;
    }

    public function getFullname(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function generateName(): string
    {
        return mb_strtoupper(mb_substr($this->lastname, 0, 1)) .
            mb_substr($this->lastname, 1, mb_strlen($this->lastname)) . ' ' .
            mb_strtoupper(mb_substr($this->firstname, 0, 1)) . '.' .
            ($this->middlename ? ' ' . mb_strtoupper(mb_substr($this->middlename, 0, 1)) . '.' : '');
    }

    public function generateNick(): string
    {
        if (empty($this->lastname)) {
            return mb_strtoupper(mb_substr($this->firstname, 0, 3));
        }
        return mb_strtoupper(mb_substr($this->lastname, 0, 3));
    }
}