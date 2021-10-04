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

use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    // Sources
    $parameters->set(Option::PATHS, [__DIR__.'/src', __DIR__.'/ecs.php', __DIR__.'/rector.php']);

    // Skip some stuff
    $parameters->set(Option::SKIP, [__DIR__.'/src/Kernel.php', __DIR__.'/tests/bootstrap.php']);

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMFONY);
    $containerConfigurator->import(SetList::PHP_CS_FIXER);

    // Custom configuration
    $services->get(PhpdocToCommentFixer::class)
        ->call('configure', [[
            'ignored_tags' => ['author', 'var'],
        ]])
    ;

    $services->get(TrailingCommaInMultilineFixer::class)
        ->call('configure', [[
            'elements' => ['arrays', 'arguments', 'parameters'],
        ]])
    ;
};
