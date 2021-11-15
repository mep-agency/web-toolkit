<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Contract\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractUser implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $email;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON)]
    protected array $roles = [];

    public function __construct()
    {
        $this->id = Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
