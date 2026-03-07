<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;
    protected function setUp(): void
    {
        self::bootKernel();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }
    public function testRepositoryEstAccessible(): void
    {
        $this->assertInstanceOf(UserRepository::class, $this->userRepository);
    }
    public function testUpgradePasswordLanceExceptionSiMauvaisType(): void
    {
        $mauvaisUser = new class implements \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string { return 'password'; }
            public function getUserIdentifier(): string { return 'test'; }
        };

        $this->expectException(\Symfony\Component\Security\Core\Exception\UnsupportedUserException::class);

        $this->userRepository->upgradePassword($mauvaisUser, 'nouveauMotDePasse');
    }
    public function testFindAllRetourneUnTableau(): void
    {
        $result = $this->userRepository->findAll();
        $this->assertIsArray($result);
    }
}
