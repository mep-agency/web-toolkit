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

use Mep\WebToolkitBundle\Config\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ShowHistoryController extends AbstractController
{
    #[Route('/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/history/', name: RouteName::PRIVACY_CONSENT_GET_HISTORY, methods: [Request::METHOD_GET])]
    public function __invoke(string $token): Response
    {
        return $this->json([]);
    }
}
