<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // Set target PHP version
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    // Sources
    $parameters->set(Option::PATHS, [__DIR__.'/src', __DIR__.'/tests', __DIR__.'/ecs.php', __DIR__.'/rector.php']);

    // Skip some stuff
    $parameters->set(Option::SKIP, [__DIR__.'/src/Kernel.php', __DIR__.'/tests/bootstrap.php']);

    // Ensure the Web Toolkit classes are always loaded correctly
    $parameters->set(Option::AUTOLOAD_PATHS, [__DIR__.'/vendor/mep-agency/web-toolkit-bundle/src']);

    // Enable Symfony support
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml',
    );

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(SetList::ORDER);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);

    // Custom configuration
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
};
