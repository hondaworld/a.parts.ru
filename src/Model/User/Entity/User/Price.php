<?php


namespace App\Model\User\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Price
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $email_send;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $filename;

    /**
     * @ORM\Column(type="boolean")
     */
    private $first_line;

    /**
     * @var int
     * @ORM\Column(type="string", nullable=false)
     */
    private $line;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=2)
     */
    private $order_num;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=2)
     */
    private $number_num;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=2)
     */
    private $creater_num;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=2)
     */
    private $quantity_num;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=2)
     */
    private $price_num;


    public function __construct(?string $email = '', ?string $email_send = '', ?string $filename = '', bool $first_line = false, ?int $line = 0, ?string $order_num = '', ?string $number_num = '', ?string $creater_num = '', ?string $quantity_num = '', ?string $price_num = '')
    {
        $this->email = $email ?: '';
        $this->email_send = $email_send ?: '';
        $this->filename = $filename ?: '';
        $this->first_line = $first_line;
        $this->line = $line ?: 0;
        $this->order_num = $order_num === null ? '' : $order_num;
        $this->number_num = $number_num === null ? '' : $number_num;
        $this->creater_num = $creater_num === null ? '' : $creater_num;
        $this->quantity_num = $quantity_num === null ? '' : $quantity_num;
        $this->price_num = $price_num === null ? '' : $price_num;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getEmailSend(): string
    {
        return $this->email_send;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return bool
     */
    public function isFirstLine(): bool
    {
        return $this->first_line;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getLineForRead(): int
    {
        return $this->getLine() < 1 ? 1 : $this->getLine();
    }

    /**
     * @return string
     */
    public function getOrderNum(): string
    {
        return $this->order_num;
    }

    /**
     * @return string
     */
    public function getNumberNum(): string
    {
        return $this->number_num;
    }

    /**
     * @return string
     */
    public function getCreaterNum(): string
    {
        return $this->creater_num;
    }

    /**
     * @return string
     */
    public function getQuantityNum(): string
    {
        return $this->quantity_num;
    }

    /**
     * @return string
     */
    public function getPriceNum(): string
    {
        return $this->price_num;
    }

}