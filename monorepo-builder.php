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
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\ValueObject\Option;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    global $argv;

    $isMergeCommand = array_reduce(
        $argv,
        fn ($result, $value) => $result + ($value === 'merge'),
        false
    );
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    // For "merge" command
    $parameters->set(Option::DATA_TO_APPEND, [
        ComposerJsonSection::REQUIRE_DEV => [
            'phpunit/phpunit' => '^9.5',
            'phpstan/phpstan' => '^0.12',
            'rector/rector' => '^0.11',
            'symplify/monorepo-builder' => '^9.4',
            'symplify/easy-coding-standard' => '^9.4',
        ],
    ]);
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__ . '/composer/packages',
        __DIR__ . '/composer/projects',
    ]);
    // Disable "mep-agency/symfony-website-skeleton" when merging
    if ($isMergeCommand) {
        $parameters->set(Option::PACKAGE_DIRECTORIES_EXCLUDES, [
            'symfony-web-toolkit-skeleton',
        ]);
    }

    $parameters->set(Option::DEFAULT_BRANCH_NAME, 'main');

    // Release workers - in order of execution
    $services->set(Mep\MonorepoUtils\ReleaseWorker\TemporaryReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualConflictsReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker::class);
    //$services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker::class);
};
