<?php

namespace App\Model\User\Entity\EmailStatus;

use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=UserEmailStatusRepository::class)
 * @ORM\Table(name="userEmailStatuses")
 */
class UserEmailStatus
{
    public const CHANGE_INCOME_STATUS = 4;
    public const ORDER_SENT = 2;
    public const DOCUMENT_SENT = 3;
    public const SCHET = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="userEmailStatusID")
     */
    private $userEmailStatusID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var User[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="exclude_email_statuses")
     */
    private $users;

    public function getId(): ?int
    {
        return $this->userEmailStatusID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
