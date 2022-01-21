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
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsent;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentService;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\CannotGenerateUpdatedConsentForUnexistingTokenException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\InvalidRequiredPreferencesException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\InvalidSpecsHashException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\UnmatchingConsentDataKeysException;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentManager
{
    /**
     * @var string
     */
    private const JSON_KEY_SPECS_HASH = 'specsHash';

    /**
     * @var string
     */
    private const JSON_KEY_USER_AGENT = 'userAgent';

    /**
     * @var string
     */
    private const JSON_KEY_CATEGORIES = 'categories';

    /**
     * @var string
     */
    private const JSON_KEY_SPECS = 'specs';

    /**
     * @var string
     */
    private const JSON_KEY_SERVICES = 'services';

    /**
     * @var string
     */
    private const JSON_KEY_PREFERENCES = 'preferences';

    /**
     * @var array<string, mixed>
     */
    private array $specs = [];

    public function __construct(
        private PrivacyConsentRepository $privacyConsentRepository,
        private PrivacyConsentCategoryRepository $privacyConsentCategoryRepository,
        private PrivacyConsentServiceRepository $privacyConsentServiceRepository,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSpecs(): array
    {
        if (empty($this->specs)) {
            $this->specs = [
                self::JSON_KEY_CATEGORIES => $this->privacyConsentCategoryRepository->findAllOrderedByPriority(),
                self::JSON_KEY_SERVICES => $this->privacyConsentServiceRepository->findAllOrderedByPriority(),
            ];
        }

        return $this->specs;
    }

    public function getSpecsHash(): string
    {
        return hash('sha256', Json::encode($this->getSpecs()));
    }

    /**
     * @param array<string, mixed> $clientData
     *
     * @throws CannotGenerateUpdatedConsentForUnexistingTokenException
     * @throws InvalidSpecsHashException
     * @throws UnmatchingConsentDataKeysException
     */
    public function generateConsent(array $clientData, ?Uuid $uuid = null): PrivacyConsent
    {
        if (null !== $uuid && null === $this->privacyConsentRepository->findLastByToken($uuid)) {
            throw new CannotGenerateUpdatedConsentForUnexistingTokenException();
        }

        $this->validateClientData($clientData);

        $clientData[self::JSON_KEY_SPECS] = $this->getSpecs();
        $clientData[self::JSON_KEY_USER_AGENT] = $this->requestStack->getCurrentRequest()?->headers->get('User-Agent');
        // TODO: @Lippa add further custom user data

        $privacyConsent = new PrivacyConsent($clientData, $uuid);

        $this->entityManager->persist($privacyConsent);
        $this->entityManager->flush();

        return $privacyConsent;
    }

    /**
     * @param array<string, mixed> $clientData
     *
     * @throws InvalidSpecsHashException
     * @throws UnmatchingConsentDataKeysException
     * @throws InvalidRequiredPreferencesException
     */
    private function validateClientData(array $clientData): void
    {
        $specsHash = $this->getSpecsHash();

        if ($specsHash !== $clientData[self::JSON_KEY_SPECS_HASH]) {
            throw new InvalidSpecsHashException();
        }

        /** @var PrivacyConsentService[] $services */
        $services = $this->getSpecs()[self::JSON_KEY_SERVICES];

        $specsServices = [];
        foreach ($services as $service) {
            $specsServices[] = $service->getId();
        }

        /** @var array<string, bool> $preferencesArray */
        $preferencesArray = $clientData[self::JSON_KEY_PREFERENCES];
        $clientDataServices = array_keys($preferencesArray);

        if ($specsServices !== $clientDataServices) {
            throw new UnmatchingConsentDataKeysException();
        }

        // Required check
        foreach ($this->privacyConsentServiceRepository->findRequired() as $requiredPrivacyConsentService) {
            if (! $preferencesArray[$requiredPrivacyConsentService->getId()]) {
                throw new InvalidRequiredPreferencesException($requiredPrivacyConsentService->getName());
            }
        }
    }
}
