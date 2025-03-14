<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\User;

class UserTest extends TestCase
{
    public function testgetUsername(): void
    {
        $user = new User();
        $user->setUsername('toto');
        $this->assertSame('toto', $user->getUsername());
    }
  
    // public function testsetUsername(): void
    // {
    //     $user = new User;
    //     $user->setUsername("henry");
    //     $this->assertSame("henry", $user->username);
    // }
}
