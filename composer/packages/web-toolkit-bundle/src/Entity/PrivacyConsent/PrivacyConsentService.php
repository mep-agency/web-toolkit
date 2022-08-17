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
use Symfony\Component\Uid\Uuid;
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

    #[ORM\ManyToOne(targetEntity: PrivacyConsentCategory::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private PrivacyConsentCategory $category;

    public function __construct()
    {
        $this->id = Uuid::v6();
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
     * @return array<string, array<string, string>|string>
     */
    public function jsonSerialize(): array
    {
        $names = [];
        $descriptions = [];

        foreach ($this->getTranslations() as $translation) {
            $names[$translation->getLocale()] = $translation->getName();
            $descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        ksort($names);
        ksort($descriptions);

        return [
            'id' => $this->getStringId(),
            'names' => $names,
            'descriptions' => $descriptions,
            'category' => $this->category->getStringId(),
        ];
    }
}
