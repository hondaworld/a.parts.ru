<?php

namespace App\Tests\Model\User\User\Review;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertEquals('', $user->getReview()->getReviewUrl());
        $this->assertFalse($user->getReview()->isReview());
        $this->assertFalse($user->getReview()->isReviewSend());
    }
}