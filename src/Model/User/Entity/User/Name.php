<?php


namespace App\Model\User\Entity\User;

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

    public function __construct(string $firstname, ?string $lastname, ?string $middlename)
    {
        Assert::notEmpty($firstname);
        $this->firstname = $firstname;
        $this->lastname = $lastname ?: '';
        $this->middlename = $middlename ?: '';
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

    public function getPassportname(): string
    {
        return  $this->lastname . ' ' . $this->firstname . ($this->middlename ?  ' ' . $this->middlename : '');
    }

    public function generateName(): string
    {
        $name = '';
        if ($this->lastname && $this->lastname != '-') {
            $name .= mb_strtoupper(mb_substr($this->lastname, 0, 1)) .
                mb_substr($this->lastname, 1, mb_strlen($this->lastname)) . ' ' .
                mb_strtoupper(mb_substr($this->firstname, 0, 1)) . '.';
        } else {
            $name .= mb_strtoupper(mb_substr($this->firstname, 0, 1)) .
                mb_substr($this->firstname, 1, mb_strlen($this->firstname));
        }

        $name .= ($this->middlename ? ' ' . mb_strtoupper(mb_substr($this->middlename, 0, 1)) . '.' : '');

        return $name;
    }
}