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

namespace Mep\WebToolkitBundle\Contract\Entity;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait as OriginalTranslatableTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
trait TranslatableTrait
{
    use OriginalTranslatableTrait;

    /**
     * @var Collection|TranslationInterface[]
     */
    #[Assert\Valid]
    protected $translations;

    /**
     * @see mergeNewTranslations
     *
     * @var Collection|TranslationInterface[]
     */
    #[Assert\Valid]
    protected $newTranslations;

    public function __call(string $name, array $arguments)
    {
        return PropertyAccess::createPropertyAccessor()->getValue(
            $this->getTranslations()
                ->get($this->currentLocale) ??
            $this->getTranslations()
                ->first(),
            $name,
        );
    }
}
