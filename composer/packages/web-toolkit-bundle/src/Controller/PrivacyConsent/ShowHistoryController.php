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

namespace Mep\WebToolkitBundle\Controller\PrivacyConsent;

use Mep\WebToolkitBundle\Contract\Controller\AbstractMwtController;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ShowHistoryController extends AbstractMwtController
{
    public function __construct(
        private PrivacyConsentRepository $privacyConsentRepository,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(string $token): Response
    {
        $token = Uuid::fromString($token);

        // TODO: @Alllle finish this
        return $this->json($this->privacyConsentRepository->findAllByToken($token));
    }
}
