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
use LogicException;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsent;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentService;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PublicKey;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\InvalidUserConsentDataException;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Nette\Utils\Json;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\RSA;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentManager
{
    /**
     * @var string
     */
    private const JSON_KEY_PUBLIC_KEY = 'publicKey';

    /**
     * @var string
     */
    private const JSON_KEY_PUBLIC_KEY_HASH = 'publicKeyHash';

    /**
     * @var string
     */
    private const JSON_KEY_SIGNATURE = 'signature';

    /**
     * @var string
     */
    private const JSON_KEY_DATA = 'data';

    /**
     * @var string
     */
    private const JSON_KEY_PREVIOUS_CONSENT_DATA_HASH = 'previousConsentDataHash';

    /**
     * @var string
     */
    private const JSON_KEY_TIMESTAMP = 'timestamp';

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

    private PrivateKey $privateKeyObject;

    /**
     * @var array<string, mixed>
     */
    private array $specs = [];

    public function __construct(
        private string $privateKey,
        private int $timestampTolerance,
        private PrivacyConsentRepository $privacyConsentRepository,
        private PrivacyConsentCategoryRepository $privacyConsentCategoryRepository,
        private PrivacyConsentServiceRepository $privacyConsentServiceRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getPrivateKeyObject(): PrivateKey
    {
        if (! isset($this->privateKeyObject)) {
            $this->privateKeyObject = RSA::loadPrivateKey($this->privateKey);
        }

        return $this->privateKeyObject;
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

    /**
     * @param null|array<string, mixed> $specs
     */
    public function getSpecsHash(?array $specs = null): string
    {
        return hash('sha256', Json::encode($specs ?? $this->getSpecs()));
    }

    /**
     * @param array<string, string> $requestContent
     *
     * @throws InvalidUserConsentDataException
     */
    public function generateConsent(array $requestContent): PrivacyConsent
    {
        $publicKeyRepository = $this->entityManager->getRepository(PublicKey::class);

        if (isset($requestContent[self::JSON_KEY_PUBLIC_KEY])) {
            $userPublicKey = new PublicKey($requestContent[self::JSON_KEY_PUBLIC_KEY]);
        } else {
            $userPublicKey = $publicKeyRepository->find($requestContent[self::JSON_KEY_PUBLIC_KEY_HASH]) ??
                throw new InvalidUserConsentDataException(
                    InvalidUserConsentDataException::CANNOT_UPDATE_CONSENT_FOR_UNEXISTING_PUBLIC_KEY,
                );
        }

        $privacyConsent = new PrivacyConsent(
            $userPublicKey,
            $requestContent[self::JSON_KEY_SIGNATURE],
            $requestContent[self::JSON_KEY_DATA],
        );

        if (! $privacyConsent->verifyUserSignature()) {
            throw new InvalidUserConsentDataException(InvalidUserConsentDataException::INVALID_SIGNATURE);
        }

        $this->validateClientData(
            $requestContent[self::JSON_KEY_DATA],
            $this->privacyConsentRepository->findLatestByPublicKey($userPublicKey),
        );

        $systemPrivateKey = $this->getPrivateKeyObject();
        $systemPublicKey = new PublicKey((string) $systemPrivateKey->getPublicKey());
        $systemPublicKey = $publicKeyRepository->find($systemPublicKey->getHash()) ?? $systemPublicKey;

        $privacyConsent->setSystemSignature(base64_encode(
            $this->getPrivateKeyObject()
                ->sign($requestContent[self::JSON_KEY_DATA]),
        ), $systemPublicKey);

        $this->entityManager->persist($privacyConsent);
        $this->entityManager->flush();

        return $privacyConsent;
    }

    /**
     * @throws InvalidUserConsentDataException
     */
    private function validateClientData(string $jsonData, ?PrivacyConsent $latestPrivacyConsent): void
    {
        /** @var array<string, mixed> $data */
        $data = Json::decode($jsonData, Json::FORCE_ARRAY);
        /** @var array<string, mixed> $dataSpecs */
        $dataSpecs = $data[self::JSON_KEY_SPECS];
        /** @var array<string, mixed> $latestConsentData */
        $latestConsentData = $latestPrivacyConsent instanceof PrivacyConsent ?
            Json::decode($latestPrivacyConsent->getData(), Json::FORCE_ARRAY) : null;

        $this->validatePreviousConsent($data, $latestPrivacyConsent);
        $this->validateTimestamp($data, $latestConsentData);

        if ($this->getSpecsHash() !== $this->getSpecsHash($dataSpecs)) {
            throw new InvalidUserConsentDataException(InvalidUserConsentDataException::INVALID_SPECS_HASH);
        }

        /** @var PrivacyConsentService[] $services */
        $services = $this->getSpecs()[self::JSON_KEY_SERVICES];

        $specsServices = [];
        foreach ($services as $service) {
            $specsServices[] = $service->getId();
        }

        /** @var array<string, bool> $preferencesArray */
        $preferencesArray = $data[self::JSON_KEY_PREFERENCES];
        $dataServices = array_keys($preferencesArray);

        if ($specsServices !== $dataServices) {
            throw new InvalidUserConsentDataException(InvalidUserConsentDataException::UNMATCHING_SERVICES);
        }

        // Required check
        foreach ($this->privacyConsentServiceRepository->findRequired() as $requiredPrivacyConsentService) {
            if (! $preferencesArray[$requiredPrivacyConsentService->getId()]) {
                throw new InvalidUserConsentDataException(
                    InvalidUserConsentDataException::INVALID_REQUIRED_PREFERENCES,
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws InvalidUserConsentDataException
     */
    private function validatePreviousConsent(array $data, ?PrivacyConsent $latestPrivacyConsent): void
    {
        if (! $latestPrivacyConsent instanceof PrivacyConsent && null !== $data[self::JSON_KEY_PREVIOUS_CONSENT_DATA_HASH]) {
            throw new InvalidUserConsentDataException(
                InvalidUserConsentDataException::PREVIOUS_CONSENT_HASH_HAS_TO_BE_NULL,
            );
        }

        if ($latestPrivacyConsent instanceof PrivacyConsent) {
            $previousConsentData = $latestPrivacyConsent->getData();
            $previousConsentDataHash = hash('sha256', $previousConsentData);

            if ($previousConsentDataHash !== $data[self::JSON_KEY_PREVIOUS_CONSENT_DATA_HASH]) {
                throw new InvalidUserConsentDataException(
                    InvalidUserConsentDataException::PREVIOUS_CONSENT_HASH_DOES_NOT_MATCH,
                );
            }
        }
    }

    /**
     * @param array<string, mixed>      $data
     * @param null|array<string, mixed> $latestConsentData
     *
     * @throws InvalidUserConsentDataException
     */
    private function validateTimestamp(array $data, ?array $latestConsentData): void
    {
        if (! isset($data[self::JSON_KEY_TIMESTAMP])) {
            throw new InvalidUserConsentDataException(InvalidUserConsentDataException::UNSET_TIMESTAMP);
        }

        if (! is_int($data[self::JSON_KEY_TIMESTAMP])) {
            throw new InvalidUserConsentDataException(InvalidUserConsentDataException::TIMESTAMP_IS_NOT_INTEGER);
        }

        $userTimestamp = $data[self::JSON_KEY_TIMESTAMP];

        if ($userTimestamp < $_SERVER['REQUEST_TIME'] - $this->timestampTolerance || $userTimestamp > $_SERVER['REQUEST_TIME'] + 1) {
            throw new InvalidUserConsentDataException(
                InvalidUserConsentDataException::TIMESTAMP_IS_NOT_IN_VALID_RANGE,
            );
        }

        if (null === $latestConsentData) {
            return;
        }

        if (! isset($latestConsentData[self::JSON_KEY_TIMESTAMP])) {
            throw new LogicException('Latest consent does not have "timestamp" key in its data.');
        }

        if (! is_int($latestConsentData[self::JSON_KEY_TIMESTAMP])) {
            throw new LogicException("Latest consent's timestamp in not an integer.");
        }

        if ($userTimestamp < $latestConsentData[self::JSON_KEY_TIMESTAMP] - 1) {
            throw new InvalidUserConsentDataException(
                InvalidUserConsentDataException::TIMESTAMP_IS_PRIOR_TO_LATEST_CONSENT,
            );
        }
    }
}
