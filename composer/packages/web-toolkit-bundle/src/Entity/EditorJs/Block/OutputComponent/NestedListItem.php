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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Stringable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Uid\Uuid;

/**
 * @final You should not extend this class.
 *
 * @internal
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_nested_list_item')]
class NestedListItem implements JsonSerializable, Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    /**
     * @noRector
     *
     * @var Collection<int, NestedListItem>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: [
        'persist',
        'remove',
    ], fetch: 'EAGER', orphanRemoval: true)]
    #[ORM\OrderBy([
        'uuid' => 'ASC',
    ])]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'items')]
    #[ORM\JoinColumn(referencedColumnName: 'uuid')]
    #[Ignore]
    private ?self $parent;

    /**
     * @param NestedListItem[] $items
     */
    public function __construct(
        #[ORM\Column(type: Types::TEXT)]
        private string $content,
        array $items,
    ) {
        $this->uuid = Uuid::v6();
        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function __toString(): string
    {
        $plainTextTokens = [];

        if (! empty($this->content)) {
            $plainTextTokens[] = $this->content;
        }

        foreach ($this->items as $item) {
            $blockAsPlainText = (string) $item;

            if (! empty($blockAsPlainText)) {
                $plainTextTokens[] = $blockAsPlainText;
            }
        }

        return strip_tags(implode(PHP_EOL, $plainTextTokens));
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return Collection<int, NestedListItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(self $item): self
    {
        if (! $this->items->contains($item)) {
            $this->items[] = $item;
            $item->setParent($this);
        }

        return $this;
    }

    public function removeItem(self $item): self
    {
        // set the owning side to null (unless already changed)
        if ($this->items->removeElement($item) && $item->getParent() === $this) {
            $item->setParent(null);
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param null|NestedListItem $parent
     */
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return array<string, NestedListItem[]|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content,
            'items' => $this->items->toArray(),
        ];
    }
}
