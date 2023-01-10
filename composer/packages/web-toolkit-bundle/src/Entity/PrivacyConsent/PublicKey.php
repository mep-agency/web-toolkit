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
use phpseclib3\Crypt\Common\PublicKey as PublicKeyObject;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_public_key')]
class PublicKey
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $hash;

    public function __construct(
        #[ORM\Column(type: Types::TEXT, name: '`key`')]
        private string $key,
    ) {
        $this->hash = hash('sha256', $this->key);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getKey(): PublicKeyObject
    {
        return PublicKeyLoader::loadPublicKey($this->key);
    }
}
