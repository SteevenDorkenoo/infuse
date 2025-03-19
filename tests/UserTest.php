<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Comments;
use App\Entity\User;

class UserTest extends TestCase
{
    public function testgetUsername(): void
    {
        $user = new User();
        $user->setUsername('toto');
        $this->assertSame('toto', $user->getUsername());
    }

    public function testGetComments()
    {
        $user = new User();
        $this->assertEmpty($user->getComments());
    }

    public function testAddComment()
    {
        $user = new User();
        $comment = new Comments();

        $user->addComment($comment);
        $this->assertCount(1, $user->getComments());
        $this->assertTrue($user->getComments()->contains($comment));
        $this->assertSame($user, $comment->getUserId());
    }

}
