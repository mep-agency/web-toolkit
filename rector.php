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

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// Fix class not found error for Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface
require_once __DIR__.'/vendor/symplify/monorepo-builder/vendor/autoload.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // Set target PHP version
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    // Sources
    $parameters->set(Option::PATHS, [
        __DIR__.'/composer',
        __DIR__.'/ecs.php',
        __DIR__.'/monorepo-builder.php',
        __DIR__.'/rector.php',
    ]);

    // Skip some stuff
    $parameters->set(Option::SKIP, [
        AddArrayReturnDocTypeRector::class => [
            // Avoid "@return null“ comments
            __DIR__.'/composer/packages/web-toolkit-bundle/src/Form/TypeGuesser/AdminAttachmentTypeGuesser.php',
            __DIR__.'/composer/packages/web-toolkit-bundle/src/Form/TypeGuesser/AdminEditorJsTypeGuesser.php',
        ],
        CallableThisArrayToAnonymousFunctionRector::class => [
            // Cannot use values of type "Closure" in service configuration files.
            __DIR__.'/composer/packages/mwt-k8s-cli/config/services.php',
        ],
    ]);

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
