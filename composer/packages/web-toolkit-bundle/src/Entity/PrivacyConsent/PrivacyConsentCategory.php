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

namespace Mep\WebToolkitBundle\Entity\PrivacyConsent;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Mep\WebToolkitBundle\Contract\Entity\TranslatableTrait;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Stringable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method string getName()
 * @method string getDescription()
 *
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity(repositoryClass: PrivacyConsentCategoryRepository::class)]
#[ORM\Table(name: 'mwt_privacy_consent_category')]
class PrivacyConsentCategory implements TranslatableInterface, Stringable, JsonSerializable
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\Length(max: 32)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $stringId;

    #[ORM\Column(type: Types::SMALLINT, options: [
        'default' => 10,
    ])]
    #[Assert\NotNull]
    private int $priority = 10;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Assert\NotNull]
    private bool $required = false;

    public function __construct()
    {
        $this->id = Uuid::v6();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStringId(): string
    {
        return $this->stringId;
    }

    public function setStringId(string $stringId): self
    {
        $this->stringId = $stringId;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return array<string, bool|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->stringId,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'required' => $this->required,
        ];
    }
}
