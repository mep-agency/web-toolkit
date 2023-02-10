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
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;

// Fix class not found error for Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface
require_once __DIR__.'/vendor/symplify/monorepo-builder/vendor/autoload.php';

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();

    // Set target PHP version
    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    // Sources
    $rectorConfig->paths([
        __DIR__.'/composer',
        __DIR__.'/ecs.php',
        __DIR__.'/monorepo-builder.php',
        __DIR__.'/rector.php',
    ]);

    // Skip some stuff
    $rectorConfig->skip([
        AddArrayReturnDocTypeRector::class => [
            // Avoid "@return null“ comments
            __DIR__.'/composer/packages/web-toolkit-bundle/src/Form/TypeGuesser/AdminAttachmentTypeGuesser.php',
            __DIR__.'/composer/packages/web-toolkit-bundle/src/Form/TypeGuesser/AdminEditorJsTypeGuesser.php',
        ],
        CallableThisArrayToAnonymousFunctionRector::class => [
            // Cannot use values of type "Closure" in service configuration files.
            __DIR__.'/composer/packages/web-toolkit-k8s-cli/config/services.php',
        ],
    ]);

    // Define what rule sets will be applied
    $rectorConfig->sets([
        SetList::PHP_81,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
    ]);

    // Custom configuration
    $rectorConfig->importNames();
};
