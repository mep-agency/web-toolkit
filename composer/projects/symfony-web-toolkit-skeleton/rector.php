<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->paths([__DIR__.'/src', __DIR__.'/tests', __DIR__.'/ecs.php', __DIR__.'/rector.php']);

    $rectorConfig->skip([__DIR__.'/src/Kernel.php', __DIR__.'/tests/bootstrap.php']);

    $rectorConfig->autoloadPaths([__DIR__.'/vendor/mep-agency/web-toolkit-bundle/src']);

    $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

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

    $rectorConfig->importNames();
};
