<?php

namespace App\Model\User\Entity\Comment;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserCommentRepository::class)
 * @ORM\Table(name="user_comments")
 */
class UserComment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="commentID")
     */
    private $commentID;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="user_comments")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="user_comments")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    public function __construct(Manager $manager, User $user, string $comment)
    {
        $this->manager = $manager;
        $this->user = $user;
        $this->comment = $comment;
        $this->dateofadded = new \DateTime();
    }

    public function update(string $comment)
    {
        $this->comment = $comment;
    }

    public function getId(): ?int
    {
        return $this->commentID;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }
}
