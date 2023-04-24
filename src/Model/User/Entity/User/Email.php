<?php


namespace App\Model\User\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Email
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $email_send;

    /**
     * @ORM\Column(type="boolean", name="isEmail")
     */
    private $isEmail;

    /**
     * @ORM\Column(type="boolean", name="email_send_isActive")
     */
    private $email_send_isActive;

    public function __construct(string $value, bool $isEmail, bool $email_send_isActive)
    {
        $this->email_send = $value;
        $this->isEmail = $isEmail;
        $this->email_send_isActive = $email_send_isActive;
    }

    public function getValue(): string
    {
        return $this->email_send;
    }

    public function isNotification(): bool
    {
        return $this->isEmail;
    }

    public function isActive(): bool
    {
        return $this->email_send_isActive;
    }

    public function isEqual(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }

    public function isActivated(): bool
    {
        return $this->isEmail;
    }

    public function getValueWithCheck(): ?string
    {
        if (!empty($this->getValue()) && $this->isActivated() && $this->isNotification()) return $this->getValue();
        return null;
    }
}