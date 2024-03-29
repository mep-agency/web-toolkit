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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Stringable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_content')]
#[ORM\HasLifecycleCallbacks]
class EditorJsContent implements JsonSerializable, Stringable
{
    /**
     * @var string
     */
    private const CURRENT_TIME_PLACEHOLDER = 'NOW()';

    /**
     * @var string
     */
    private const EDITORJS_LIB_VERSION = '2.22.2';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    /**
     * @var Block[]|Collection<int, Block>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Block::class, cascade: [
        'persist',
        'remove',
    ], fetch: 'EAGER', orphanRemoval: true)]
    #[ORM\OrderBy([
        'uuid' => 'ASC',
    ])]
    #[Valid]
    private Collection $blocks;

    #[ORM\Column(type: Types::TEXT)]
    private string $plainText = '';

    public function __construct(
        #[ORM\Column(type: Types::BIGINT)]
        private string $time = self::CURRENT_TIME_PLACEHOLDER,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $version = self::EDITORJS_LIB_VERSION,
    ) {
        $this->id = Uuid::v6();
        $this->blocks = new ArrayCollection();

        if (self::CURRENT_TIME_PLACEHOLDER === $this->time) {
            $this->time = strval(floor(microtime(true) * 1000));
        }
    }

    public function __toString(): string
    {
        $plainTextTokens = [];

        foreach ($this->blocks as $block) {
            $blockAsPlainText = (string) $block;

            if (! empty($blockAsPlainText)) {
                $plainTextTokens[] = $blockAsPlainText;
            }
        }

        return strip_tags(implode(PHP_EOL, $plainTextTokens));
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(Block $block): self
    {
        if (! $this->blocks->contains($block)) {
            $this->blocks[] = $block;
            $block->setParent($this);
        }

        return $this;
    }

    public function removeBlock(Block $block): self
    {
        $this->blocks->removeElement($block);

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatePlainText(): string
    {
        $this->plainText = (string) $this;

        return $this->plainText;
    }

    /**
     * @return array<string, int|mixed|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'time' => (int) $this->time,
            'version' => $this->version,
            'blocks' => $this->blocks->toArray(),
        ];
    }
}
