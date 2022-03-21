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
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_privacy_consent_category_translation')]
class PrivacyConsentCategoryTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 32)]
    #[Assert\Length(max: 32)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $description;

    public function __construct()
    {
        $this->id = Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
