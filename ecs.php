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

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    // Sources
    $ecsConfig->paths([
        __DIR__.'/composer',
        __DIR__.'/ecs.php',
        __DIR__.'/monorepo-builder.php',
        __DIR__.'/rector.php',
    ]);

    // Skip some stuff
    $ecsConfig->skip([
        HeaderCommentFixer::class => [__DIR__.'/composer/projects/symfony-web-toolkit-skeleton/*'],
    ]);

    // Define what rule sets will be applied
    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::PSR_12,
        SetList::SYMFONY,
        SetList::PHP_CS_FIXER,
    ]);

    // Custom configuration
    $ecsConfig->rules([LineLengthFixer::class, DocBlockLineLengthFixer::class]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => trim(
            implode(
                "\n",
                array_map(
                    fn ($line) => trim($line, '/* '),
                    explode("\n", (string) file_get_contents(__DIR__.'/license-header-template.txt')),
                ),
            ),
        ),
        'location' => 'after_open',
    ]);

    $ecsConfig->ruleWithConfiguration(PhpdocToCommentFixer::class, [
        'ignored_tags' => ['author', 'var', 'phpstan-ignore-next-line'],
    ]);

    $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arrays', 'arguments', 'parameters'],
    ]);
};
