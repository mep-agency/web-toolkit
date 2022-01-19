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
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_CREATE_CONTROLLER)
        ->methods([Request::METHOD_POST])
    ;

    $routingConfigurator->add(
        RouteName::PRIVACY_CONSENT_GET,
        $privacyConsentUrlPrefix.'/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/',
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
        $privacyConsentUrlPrefix.'/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/history/',
    )
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_SHOW_HISTORY_CONTROLLER)
        ->methods([Request::METHOD_GET])
    ;

    $routingConfigurator->add(
        RouteName::PRIVACY_CONSENT_UPDATE,
        $privacyConsentUrlPrefix.'/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/',
    )
        ->controller(WebToolkitBundle::SERVICE_PRIVACY_UPDATE_CONTROLLER)
        ->methods([Request::METHOD_POST])
    ;
};
