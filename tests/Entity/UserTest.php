<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testSetAndGetPrenom(): void
    {
        $user = new User();
        $user->setPrenom('Agnès');

        $this->assertEquals('Agnès', $user->getPrenom());
    }

    public function testRolesParDefaut(): void
    {
        $user = new User();

        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
