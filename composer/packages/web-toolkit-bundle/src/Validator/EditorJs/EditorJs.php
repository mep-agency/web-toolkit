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

namespace Mep\WebToolkitBundle\Validator\EditorJs;

use Attribute;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Attaches;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\CallToAction;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Header;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Image;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Paragraph;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Quote;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Raw;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Table;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Warning;
use Mep\WebToolkitBundle\Exception\InvalidConfigurationException;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class EditorJs extends Constraint
{
    /**
     * @var array<string, OptionsResolver>
     */
    private static array $optionsResolvers = [];

    /**
     * @param string[]             $enabledTools
     * @param string[]             $disabledTools
     * @param array<string, mixed> $options
     */
    public function __construct(
        public array $enabledTools = [],
        public array $disabledTools = [],
        public array $options = [],
    ) {
        parent::__construct();

        if (! empty($this->enabledTools) && ! empty($this->disabledTools)) {
            throw new InvalidConfigurationException(
                'EditorJs values cannot define both "enabledTools" and "disabledTools", please use one of them.',
            );
        }

        // Enable all tools by default
        if (empty($this->enabledTools)) {
            $this->enabledTools = Block::getSupportedClasses();
        } else {
            $unknownTools = array_diff($this->enabledTools, Block::getSupportedClasses());

            if (! empty($unknownTools)) {
                throw new InvalidConfigurationException('Invalid EditorJs configuration: unknown tool(s): "'.implode(
                    '", "',
                    $unknownTools,
                ).'" (enabled).');
            }
        }

        $unknownTools = array_diff($this->disabledTools, Block::getSupportedClasses());

        if (! empty($unknownTools)) {
            throw new InvalidConfigurationException('Invalid EditorJs configuration: unknown tool(s): "'.implode(
                '", "',
                $unknownTools,
            ).'" (disabled).');
        }

        // Remove disabled tools (if any)
        foreach ($this->disabledTools as $disabledTool) {
            $key = array_search($disabledTool, $this->enabledTools, true);

            if (false !== $key) {
                unset($this->enabledTools[$key]);
            }
        }

        if (! in_array(Paragraph::class, $this->enabledTools, true)) {
            throw new InvalidConfigurationException(
                'Invalid EditorJs configuration: the "paragraph" tool is mandatory.',
            );
        }

        $this->buildOptionResolvers();
        $this->resolveOptions();
    }

    private function buildOptionResolvers(): void
    {
        if (! empty(self::$optionsResolvers)) {
            return;
        }

        $associativeArrayOfScalarValuesValidator = Validation::createIsValidCallable(
            new AssociativeArrayOfScalarValues(),
        );

        foreach (Block::getSupportedClasses() as $tool) {
            $optionsResolver = new OptionsResolver();

            switch ($tool) {
                case Header::class:
                    // see https://github.com/editor-js/header#config-params
                    $optionsResolver->define('placeholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    $optionsResolver->define('levels')
                        ->default([1, 2, 3])
                        ->allowedTypes('array')
                    ;

                    $optionsResolver->define('defaultLevel')
                        ->default(1)
                        ->allowedTypes('int')
                    ;

                    break;
                case Quote::class:
                    // see https://github.com/editor-js/quote#config-params
                    $optionsResolver->define('quotePlaceholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    $optionsResolver->define('captionPlaceholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    break;
                case Warning::class:
                    // see https://github.com/editor-js/warning#config-params
                    $optionsResolver->define('titlePlaceholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    $optionsResolver->define('messagePlaceholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    break;
                case Image::class:
                    // see https://github.com/editor-js/image#config-params
                    $optionsResolver->define('captionPlaceholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    $optionsResolver->define('buttonContent')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    // Max file size in bytes (MWT custom option)
                    $optionsResolver->define('maxSize')
                        ->default(null)
                        ->allowedTypes('int', 'null')
                    ;

                    // Options for FileStorage processors (MWT custom option)
                    $optionsResolver->define('processorsOptions')
                        ->default([])
                        ->allowedTypes('array')
                    ;
                    $optionsResolver->setAllowedValues('processorsOptions', $associativeArrayOfScalarValuesValidator);

                    break;
                case Table::class:
                    // see https://github.com/editor-js/table#config-params
                    $optionsResolver->define('rows')
                        ->default(2)
                        ->allowedTypes('int')
                    ;

                    $optionsResolver->define('cols')
                        ->default(2)
                        ->allowedTypes('int')
                    ;

                    break;
                case Attaches::class:
                    // see https://github.com/editor-js/attaches#config-params
                    $optionsResolver->define('buttonText')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    $optionsResolver->define('errorMessage')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    // Max file size in bytes (MWT custom option)
                    $optionsResolver->define('maxSize')
                        ->default(null)
                        ->allowedTypes('int', 'null')
                    ;

                    // Options for FileStorage processors (MWT custom option)
                    $optionsResolver->define('processorsOptions')
                        ->default([])
                        ->allowedTypes('array')
                    ;
                    $optionsResolver->setAllowedValues('processorsOptions', $associativeArrayOfScalarValuesValidator);

                    break;
                case Raw::class:
                    // see https://github.com/editor-js/raw#config-params
                    $optionsResolver->define('placeholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    break;
                case CallToAction::class:
                    $optionsResolver->define('validCssPresets')
                        ->default(null)
                        ->allowedTypes('array', 'null')
                    ;

                    break;
            }

            self::$optionsResolvers[$tool] = $optionsResolver;
        }
    }

    private function resolveOptions(): void
    {
        $optionsResolver = new OptionsResolver();

        foreach ($this->enabledTools as $enabledTool) {
            $optionsResolver->define($enabledTool)
                ->default([])
                ->allowedTypes('array')
            ;
        }

        try {
            $this->options = $optionsResolver->resolve($this->options);
        } catch (UndefinedOptionsException $undefinedOptionsException) {
            throw new InvalidConfigurationException(
                'Invalid EditorJs configuration: '.$undefinedOptionsException->getMessage().' Did you forget to enable the tool(s)?',
            );
        }

        foreach ($this->options as $toolName => $toolOptions) {
            try {
                $this->options[(string) $toolName] = self::$optionsResolvers[$toolName]->resolve($toolOptions);
            } catch (InvalidOptionsException $invalidOptionsException) {
                throw new InvalidConfigurationException(
                    'Invalid EditorJs tool configuration ('.$toolName.'): '.$invalidOptionsException->getMessage(),
                );
            }
        }
    }
}
