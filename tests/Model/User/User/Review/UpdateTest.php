<?php

namespace App\Tests\Model\User\User\Review;

use App\Model\User\Entity\User\Review;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $user->updateReview(new Review('https://ya.ru', true, true));

        $this->assertEquals('https://ya.ru', $user->getReview()->getReviewUrl());
        $this->assertTrue($user->getReview()->isReview());
        $this->assertTrue($user->getReview()->isReviewSend());
    }

    public function testSent(): void
    {
        $user = (new UserBuilder())->build();

        $user->getReview()->reviewSent();

        $this->assertEquals('', $user->getReview()->getReviewUrl());
        $this->assertFalse($user->getReview()->isReview());
        $this->assertTrue($user->getReview()->isReviewSend());
    }
}