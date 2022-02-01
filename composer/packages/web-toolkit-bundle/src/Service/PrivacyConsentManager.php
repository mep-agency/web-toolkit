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
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PublicKey;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\CannotGenerateUpdatedConsentForUnexistingPublicKeyException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\nvalidRequiredPreferencesException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\InvalidSpecsHashException;
use Mep\WebToolkitBundle\Exception\PrivacyConsent\UnmatchingConsentDataKeysException;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Nette\Utils\Json;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\RSA;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentManager
{
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

    private PrivateKey $privateKeyObject;

    /**
     * @var array<string, mixed>
     */
    private array $specs = [];

    public function __construct(
        private string                           $privateKey,
        private int                              $timestampTolerance,
        private PrivacyConsentRepository         $privacyConsentRepository,
        private PrivacyConsentCategoryRepository $privacyConsentCategoryRepository,
        private PrivacyConsentServiceRepository  $privacyConsentServiceRepository,
        private RequestStack                     $requestStack,
        private EntityManagerInterface           $entityManager,
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
     * @throws CannotGenerateUpdatedConsentForUnexistingPublicKeyException
     * @throws InvalidSpecsHashException
     * @throws UnmatchingConsentDataKeysException
     */
    public function generateConsent(array $requestContent): PrivacyConsent
    {
        $publicKeyRepository = $this->entityManager->getRepository(PublicKey::class);
        
        if (isset($requestContent['publicKey'])) {
            $userPublicKey = new PublicKey($requestContent['publicKey']);
        } else {
            $userPublicKey = $publicKeyRepository->find($requestContent['publicKeyHash']) ??
                throw new CannotGenerateUpdatedConsentForUnexistingPublicKeyException();
        }

        $privacyConsent = new PrivacyConsent($userPublicKey, $requestContent['signature'], $requestContent['data']);

        if (! $privacyConsent->verifyUserSignature()) {
            throw new CannotGenerateUpdatedConsentForUnexistingPublicKeyException(); // TODO: @Alle Invalid signature
        }

        $this->validateClientData(
            $requestContent['data'],
            $this->privacyConsentRepository->findLatestByPublicKey($userPublicKey),
        );

        $systemPrivateKey = $this->getPrivateKeyObject();
        $systemPublicKey = new PublicKey((string) $systemPrivateKey->getPublicKey());
        $systemPublicKey = $publicKeyRepository->find($systemPublicKey->getHash()) ?? $systemPublicKey;
        
        $privacyConsent->setSystemSignature(bin2hex(
            $this->getPrivateKeyObject()
                ->sign($requestContent['data']),
        ), $systemPublicKey);

        $this->entityManager->persist($privacyConsent);
        $this->entityManager->flush();

        return $privacyConsent;
    }

    /**
     * @throws InvalidSpecsHashException
     * @throws UnmatchingConsentDataKeysException
     * @throws nvalidRequiredPreferencesException
     */
    private function validateClientData(string $jsonData, ?PrivacyConsent $latestPrivacyConsent): void
    {
        $data = Json::decode($jsonData, Json::FORCE_ARRAY);
        $latestConsentData = $latestPrivacyConsent instanceof PrivacyConsent ?
            Json::decode($latestPrivacyConsent->getData(), Json::FORCE_ARRAY) : null;

        $this->validateTimestamp($data, $latestConsentData);
        $this->validateUserAgent($data);

        if ($this->getSpecsHash() !== $this->getSpecsHash($data[self::JSON_KEY_SPECS])) {
            throw new InvalidSpecsHashException();
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
            throw new UnmatchingConsentDataKeysException();
        }

        // Required check
        foreach ($this->privacyConsentServiceRepository->findRequired() as $requiredPrivacyConsentService) {
            if (! $preferencesArray[$requiredPrivacyConsentService->getId()]) {
                throw new nvalidRequiredPreferencesException($requiredPrivacyConsentService->getName());
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param null|array<string, mixed> $latestConsentData
     */
    private function validateTimestamp(array $data, ?array $latestConsentData): void
    {
        if (! isset($data['timestamp'])) {
            throw new \RuntimeException('no timestamp'); // TODO
        }

        if (! is_int($data['timestamp'])) {
            throw new \RuntimeException('no int'); // TODO
        }

        $userTimestamp = $data['timestamp'];

        if ($userTimestamp < $_SERVER['REQUEST_TIME'] - $this->timestampTolerance || $userTimestamp > $_SERVER['REQUEST_TIME'] + 1) {
            throw new \RuntimeException('mi stai fottendo'); // TODO
        }

        if (null === $latestConsentData) {
            return;
        }

        if (! isset($latestConsentData['timestamp'])) {
            throw new \RuntimeException('no timestamp'); // TODO diverse diosvizzero
        }

        if (! is_int($latestConsentData['timestamp'])) {
            throw new \RuntimeException('no int'); // TODO diverse diosvizzero
        }

        if ($userTimestamp > $latestConsentData['timestamp'] + 1) {
            throw new \RuntimeException('troppo in fretta'); // TODO
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateUserAgent(array $data): void
    {
        if (! isset($data['userAgent'])) {
            throw new \RuntimeException('no user agent'); // TODO
        }

        if (! is_string($data['userAgent'])) {
            throw new \RuntimeException('no string'); // TODO
        }

        if ($data['userAgent'] !== $this->requestStack->getCurrentRequest()?->headers->get('User-Agent')) {
            throw new \RuntimeException('mi stai rifottendo'); // TODO
        }
    }
}
