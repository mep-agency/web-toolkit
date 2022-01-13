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
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method string getName()
 * @method string getDescription()
 *
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity(repositoryClass: PrivacyConsentServiceRepository::class)]
#[ORM\Table(name: 'mwt_privacy_consent_service')]
class PrivacyConsentService implements TranslatableInterface, JsonSerializable
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\Length(max: 32)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $id;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotNull]
    private int $priority;

    #[ORM\ManyToOne(targetEntity: PrivacyConsentCategory::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PrivacyConsentCategory $category;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    public function getCategory(): PrivacyConsentCategory
    {
        return $this->category;
    }

    public function setCategory(PrivacyConsentCategory $privacyConsentCategory): self
    {
        $this->category = $privacyConsentCategory;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'category' => $this->category->getId(),
        ];
    }
}
