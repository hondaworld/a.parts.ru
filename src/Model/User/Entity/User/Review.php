<?php


namespace App\Model\User\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Review
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, name="reviewUrl")
     */
    private $reviewUrl;

    /**
     * @ORM\Column(type="boolean", name="isReviewSend")
     */
    private $isReviewSend;

    /**
     * @ORM\Column(type="boolean", name="isReview")
     */
    private $isReview;

    public function __construct(?string $reviewUrl = '', bool $isReviewSend = false, bool $isReview = false)
    {
        $this->reviewUrl = $reviewUrl ?: '';
        $this->isReviewSend = $isReviewSend;
        $this->isReview = $isReview;
    }

    public function reviewSent()
    {
        $this->isReviewSend = true;
    }

    /**
     * @return string
     */
    public function getReviewUrl(): string
    {
        return $this->reviewUrl;
    }

    /**
     * @return bool
     */
    public function isReviewSend(): bool
    {
        return $this->isReviewSend;
    }

    /**
     * @return bool
     */
    public function isReview(): bool
    {
        return $this->isReview;
    }


}