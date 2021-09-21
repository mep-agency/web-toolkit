<?php

namespace Mep\WebToolkitBundle\Twig;

use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EditorJsExtension extends AbstractExtension
{
    public function __construct(private Environment $environment)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('editorjs', [$this, 'toHtml'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('editorjs', [$this, 'toHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function toHtml(EditorJsContent $editorJsContent, int $startingHeadingLevel = 2): string
    {
        $blocks = [];

        foreach ($editorJsContent->getBlocks() as $block) {
            $blocks[Block::getTypeByClass($block::class)] = $block;
        }

        return $this->environment->render('@WebToolkit/front_end/editorjs/content.html.twig', [
            'content' => $editorJsContent,
            'blocks' => $blocks,
        ]);
    }
}
