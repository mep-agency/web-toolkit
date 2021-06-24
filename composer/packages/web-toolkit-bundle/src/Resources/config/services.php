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

use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use Mep\WebToolkitBundle\EventListener\ForceSingleInstanceEventListener;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableBooleanConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldPreConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure(true)
        ->autowire(true);

    // Single instance support
    $services->set(ForceSingleInstanceEventListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'prePersist',
            'priority' => 9999,
        ]);

    // Translatable support
    $services->set(TranslatableFieldPreConfigurator::class)
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => 99999]);
    $services->set(TranslatableFieldConfigurator::class)
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR);
    $services->set(TranslatableBooleanConfigurator::class)
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => -9998]);
};
