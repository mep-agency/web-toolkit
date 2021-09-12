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

namespace Mep\WebToolkitBundle\Entity\EditorJs\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\OutputComponent\NestedListItem;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/nested-list
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_nested_list')]
class NestedList extends Block
{
    /**
     * @var Collection<int, NestedListItem>
     */
    #[ORM\ManyToMany(targetEntity: NestedListItem::class, orphanRemoval: true, cascade: [
        'persist',
        'remove',
    ], fetch: 'EAGER')]
    #[ORM\JoinTable(name: 'mwt_editor_js_nested_list_nested_list_item')]
    #[ORM\JoinColumn(name: 'nested_list_id', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'nested_list_item_id', referencedColumnName: 'uuid', unique: true)]
    #[ORM\OrderBy([
        'uuid' => 'ASC',
    ])]
    #[Valid]
    private Collection $items;

    /**
     * @param NestedListItem[] $items
     */
    public function __construct(
        string $id,
        #[ORM\Column(type: 'string')]
        private string $style,
        array $items,
    ) {
        parent::__construct($id);

        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @return Collection<int, NestedListItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(NestedListItem $nestedListItem): self
    {
        if (! $this->items->contains($nestedListItem)) {
            $this->items[] = $nestedListItem;
        }

        return $this;
    }

    public function removeItem(NestedListItem $nestedListItem): self
    {
        $this->items->removeElement($nestedListItem);

        return $this;
    }

    /**
     * @return array<string, NestedListItem[]|string>
     */
    protected function getData(): array
    {
        return [
            'style' => $this->style,
            'items' => $this->items->toArray(),
        ];
    }
}
