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

namespace Mep\WebToolkitBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;

class PrivacyConsentManager
{
    private PrivacyConsentCategoryRepository $privacyConsentCategoryRepository;

    private PrivacyConsentServiceRepository $privacyConsentServiceRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->privacyConsentCategoryRepository = $this->entityManager->getRepository(PrivacyConsentCategoryRepository::class);
        $this->privacyConsentServiceRepository = $this->entityManager->getRepository(PrivacyConsentSericeRepository::class);
    }
}
