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

use Mep\WebToolkitBundle\Config\RouteName;
use Mep\WebToolkitBundle\WebToolkitBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routingConfigurator) {
    $privacyConsentUrlPrefix = '/privacy-consent';

    $routingConfigurator->add(RouteName::PRIVACY_CONSENT_CREATE, $privacyConsentUrlPrefix.'/')
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_CREATE_CONSENT_CONTROLLER)
        ->methods([Request::METHOD_POST])
    ;

    $routingConfigurator->add(
        RouteName::PRIVACY_CONSENT_GET,
        $privacyConsentUrlPrefix.'/{hash}/',
    )
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_GET_CONSENT_CONTROLLER)
        ->methods([Request::METHOD_GET])
    ;

    $routingConfigurator->add(RouteName::PRIVACY_CONSENT_GET_SPECS, $privacyConsentUrlPrefix.'/specs/')
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_GET_SPECS_CONTROLLER)
        ->methods([Request::METHOD_GET])
    ;

    $routingConfigurator->add(
        RouteName::PRIVACY_CONSENT_GET_HISTORY,
        $privacyConsentUrlPrefix.'/{hash}/history/',
    )
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_SHOW_HISTORY_CONTROLLER)
        ->methods([Request::METHOD_GET])
    ;
};
