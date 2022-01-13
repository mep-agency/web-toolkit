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

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity(PrivacyConsentRepository::class)]
#[UniqueEntity(fields: ['token', 'datetime'])]
#[ORM\Table(name: 'mwt_privacy_consent')]
class PrivacyConsent
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $token;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeInterface $datetime;

    /**
     * @param array<string, mixed> $consents
     */
    public function __construct(
        #[ORM\Column(type: Types::JSON)]
        private array $consents = [],
    ) {
        $this->id = Uuid::v6();
        $this->token = Uuid::v4();
        $this->datetime = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getToken(): Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $uuid): self
    {
        $this->token = $uuid;

        return $this;
    }

    public function getDatetime(): DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConsents(): array
    {
        return $this->consents;
    }

    /**
     * @param array<string, mixed> $consents
     */
    public function setConsents(array $consents): self
    {
        $this->consents = $consents;

        return $this;
    }
}
