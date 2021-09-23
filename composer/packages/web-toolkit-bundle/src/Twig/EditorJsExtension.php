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

namespace Mep\WebToolkitBundle\Twig;

use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EditorJsExtension extends AbstractExtension
{
    public function __construct(
        private Environment $environment,
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'editorjs',
                function (
                    EditorJsContent $editorJsContent,
                    int $startingHeadingLevel = 2,
                    string $wrapperTag = 'div',
                ): string {
                    return $this->toHtml($editorJsContent, $startingHeadingLevel, $wrapperTag);
                },
                [
                    'is_safe' => ['html'],
                ],
            ),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'editorjs',
                function (
                    EditorJsContent $editorJsContent,
                    int $startingHeadingLevel = 2,
                    string $wrapperTag = 'div',
                ): string {
                    return $this->toHtml($editorJsContent, $startingHeadingLevel, $wrapperTag);
                },
                [
                    'is_safe' => ['html'],
                ],
            ),
        ];
    }

    public function toHtml(
        EditorJsContent $editorJsContent,
        int $startingHeadingLevel = 2,
        string $wrapperTag = 'div',
    ): string {
        $blocks = [];

        foreach ($editorJsContent->getBlocks() as $block) {
            $blocks[] = [
                'type' => Block::getTypeByClass($block::class),
                'block' => $block,
            ];
        }

        return $this->environment->render('@WebToolkit/front_end/editorjs/content.html.twig', [
            'starting_heading_level' => $startingHeadingLevel,
            'wrapper_tag' => $wrapperTag,
            'content' => $editorJsContent,
            'blocks' => $blocks,
        ]);
    }
}
