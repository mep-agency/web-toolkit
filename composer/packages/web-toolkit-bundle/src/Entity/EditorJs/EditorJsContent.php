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
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_content')]
class EditorJsContent implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\OneToMany(targetEntity: Block::class, mappedBy: 'parent', orphanRemoval: true, cascade: [
        'persist',
        'remove',
    ], fetch: 'EAGER')]
    #[ORM\OrderBy([
        'uuid' => 'ASC',
    ])]
    #[Valid]
    private $blocks;

    public function __construct(
        #[ORM\Column(type: 'bigint')]
        private string $time,
        #[ORM\Column(type: 'string', length: 255)]
        private string $version,
    ) {
        $this->id = Uuid::v6();
        $this->blocks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @return Block[]|Collection
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
        if ($this->blocks->removeElement($block)) {
            // set the owning side to null (unless already changed)
            if ($block->getParent() === $this) {
                $block->setParent(null);
            }
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'time' => (int) $this->time,
            'version' => $this->version,
            'blocks' => $this->blocks->toArray(),
        ];
    }
}
