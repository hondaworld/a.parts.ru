<?php

namespace App\Tests\Model\Order\UserComment;

use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UserCommentTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $comment = 'Тестовый комментарий';

        $user->assignUserComment($manager, $comment);

        $this->assertCount(1, $user->getUserComments());

        foreach ($user->getUserComments() as $userComment) {
            $this->assertEquals($comment, $userComment->getComment());
        }
    }

    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $comment = 'Тестовый комментарий';

        $user->assignUserComment($manager, $comment);

        foreach ($user->getUserComments() as $userComment) {
            $userComment->update('Измененный комментарий');
            $this->assertEquals('Измененный комментарий', $userComment->getComment());
        }
    }
}