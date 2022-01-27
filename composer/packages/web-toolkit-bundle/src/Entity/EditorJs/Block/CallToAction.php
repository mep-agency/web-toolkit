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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_cta')]
class CallToAction extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: Types::TEXT, length: 64)]
        private string $buttonText,
        #[ORM\Column(type: Types::TEXT, length: 255)]
        private string $buttonUrl,
        #[ORM\Column(type: Types::TEXT, length: 128)]
        private ?string $additionalText = null,
        #[ORM\Column(type: Types::STRING, length: 64)]
        private ?string $cssPreset = null,
    ) {
        parent::__construct($id);
    }

    public function __toString(): string
    {
        $plainTextTokens = [];

        if (! empty($this->buttonText)) {
            $plainTextTokens[] = $this->buttonText;
        }

        if (! empty($this->additionalText)) {
            $plainTextTokens[] = $this->additionalText;
        }

        return implode(PHP_EOL, $plainTextTokens);
    }

    public function getButtonText(): string
    {
        return $this->buttonText;
    }

    public function getButtonUrl(): string
    {
        return $this->buttonUrl;
    }

    public function getAdditionalText(): ?string
    {
        return $this->additionalText;
    }

    public function getCssPreset(): ?string
    {
        return $this->cssPreset;
    }

    /**
     * @return array<string, null|string>
     */
    protected function getData(): array
    {
        return [
            'buttonText' => $this->buttonText,
            'buttonUrl' => $this->buttonUrl,
            'additionalText' => $this->additionalText,
            'cssPreset' => $this->cssPreset,
        ];
    }
}
