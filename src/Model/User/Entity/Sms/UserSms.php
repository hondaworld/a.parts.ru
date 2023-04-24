<?php

namespace App\Model\User\Entity\Sms;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserSmsRepository::class)
 * @ORM\Table(name="user_sms")
 */
class UserSms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="user_sms")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="user_sms")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $status_code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status_text;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $sms_id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $phonemob;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $sender;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    public function __construct(?Manager $manager, User $user, string $status_code, string $status_text, string $sms_id, string $phonemob, string $sender, string $text)
    {
        $this->manager = $manager;
        $this->user = $user;
        $this->status_code = $status_code;
        $this->status_text = $status_text;
        $this->sms_id = $sms_id;
        $this->phonemob = $phonemob;
        $this->sender = $sender;
        $this->text = $text;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getStatusCode(): string
    {
        return $this->status_code;
    }

    public function getStatusText(): string
    {
        return $this->status_text;
    }

    public function getSmsId(): string
    {
        return $this->sms_id;
    }

    public function getPhonemob(): string
    {
        return $this->phonemob;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
