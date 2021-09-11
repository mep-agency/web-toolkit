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

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMFONY);
    $containerConfigurator->import(SetList::PHP_CS_FIXER);
    $containerConfigurator->import(SetList::SYMPLIFY);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/composer',
        __DIR__ . '/ecs.php',
        __DIR__ . '/monorepo-builder.php',
    ]);

    $parameters->set(Option::SKIP, [
        //__DIR__ . '/src/Kernel.php',
        //__DIR__ . '/tests/bootstrap.php',
    ]);
};
