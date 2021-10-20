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

namespace Mep\WebToolkitBundle\Dto;

use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class LoginRequestProcessResultDto
{
    public function __construct(
        public AbstractUser $user,
        public LoginLinkDetails $loginLinkDetails,
    ) {
    }
}
