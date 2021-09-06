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

namespace Mep\WebToolkitBundle\Entity\EditorJs;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Uid\Uuid;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_block')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(Block::BLOCKS_MAPPING)]
#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: Block::BLOCKS_MAPPING,
)]
abstract class Block implements JsonSerializable
{
    public const BLOCKS_MAPPING = [
        // Built-in
        'paragraph' => Block\Paragraph::class,
        // @editorjs/header
        'header' => Block\Header::class,
        // @editorjs/nested-list
        'list' => Block\NestedList::class,
        // @editorjs/delimiter
        'delimiter' => Block\Delimiter::class,
        // @editorjs/quote
        'quote' => Block\Quote::class,
        // @editorjs/warning
        'warning' => Block\Warning::class,
        // @editorjs/image
        // TODO: Implement image block (EditorJs)
        //'image' => Block\Image::class,
        // @editorjs/embed
        'embed' => Block\Embed::class,
        // @editorjs/table
        'table' => Block\Table::class,
        // @editorjs/attaches
        // TODO: Implement attaches block (EditorJs)
        //'attaches' => Block\Attaches::class,
        // @editorjs/raw
        'raw' => Block\Raw::class,
    ];

    /** @var array<class-string, string> */
    private static array $reverseBlocksMapping = [];

    /** @var array<string> */
    private static array $supportedTypes = [];

    /** @var array<class-string> */
    private static array $supportedClasses = [];

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    #[ORM\ManyToOne(targetEntity: EditorJsContent::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private EditorJsContent $parent;

    /**
     * @param class-string $fqcn
     */
    public static function getTypeByClass(string $fqcn): string
    {
        if (empty(self::$reverseBlocksMapping)) {
            foreach (self::BLOCKS_MAPPING as $type => $class) {
                self::$reverseBlocksMapping[$class] = $type;
            }
        }

        return self::$reverseBlocksMapping[$fqcn];
    }

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        if (empty(self::$supportedTypes)) {
            self::$supportedTypes = array_keys(self::BLOCKS_MAPPING);
        }

        return self::$supportedTypes;
    }

    /**
     * @return class-string[]
     */
    public static function getSupportedClasses(): array
    {
        if (empty(self::$supportedClasses)) {
            self::$supportedClasses = array_values(self::BLOCKS_MAPPING);
        }

        return self::$supportedClasses;
    }

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string $id,
    ) {
        $this->uuid = Uuid::v6();
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getParent(): ?EditorJsContent
    {
        return $this->parent;
    }

    public function setParent(?EditorJsContent $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => self::getTypeByClass(static::class),
            'data' => $this->getData(),
        ];
    }

    protected abstract function getData(): array;
}
