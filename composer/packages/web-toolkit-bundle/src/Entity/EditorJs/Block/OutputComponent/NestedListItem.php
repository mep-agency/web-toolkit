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

namespace Mep\WebToolkitBundle\Entity\EditorJs\Block\OutputComponent;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Uid\Uuid;

/**
 * @final You should not extend this class.
 * @internal
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_nested_list_item')]
class NestedListItem implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * @var Collection<NestedListItem>|NestedListItem[]
     */
    #[ORM\OneToMany(targetEntity: NestedListItem::class, mappedBy: 'parent', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['uuid' => 'ASC'])]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: NestedListItem::class, inversedBy: 'items')]
    #[ORM\JoinColumn(referencedColumnName: 'uuid', nullable: true)]
    #[Ignore]
    private ?self $parent;

    /**
     * @param NestedListItem[] $items
     */
    public function __construct(
        #[ORM\Column(type: 'text')]
        private string $content,

        array $items,
    ) {
        $this->uuid = Uuid::v6();
        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return Collection<NestedListItem>|NestedListItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(NestedListItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setParent($this);
        }

        return $this;
    }

    public function removeItem(NestedListItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParent() === $this) {
                $item->setParent(null);
            }
        }

        return $this;
    }

    public function getParent(): ?NestedListItem
    {
        return $this->parent;
    }

    public function setParent(?NestedListItem $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'content' => $this->content,
            'items' => $this->items->toArray(),
        ];
    }
}
