<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    public function getId(): int|string|null;

    public function getEmail(): ?string;
}
