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

use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Nette\Utils\Json;

class PrivacyConsentManager
{
    /**
     * @var array<string, mixed>
     */
    private array $specs = [];

    public function __construct(
        private PrivacyConsentCategoryRepository $privacyConsentCategoryRepository,
        private PrivacyConsentServiceRepository $privacyConsentServiceRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSpecs(): array
    {
        if (empty($this->specs)) {
            $this->specs = [
                'categories' => $this->privacyConsentCategoryRepository->findAllOrderedByPriority(),
                'services' => $this->privacyConsentServiceRepository->findAllOrderedByPriority(),
            ];
        }

        return $this->specs;
    }

    public function getSpecsHash(): string
    {
        return hash('sha256', Json::encode($this->getSpecs()));
    }
}
