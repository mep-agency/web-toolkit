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

namespace Mep\WebToolkitBundle\Validator;

use Attribute;
use Mep\WebToolkitBundle\Exception\InvalidConfigurationException;
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
    public const PARAGRAPHS = 'paragraph';
    // @editorjs/header
    public const HEADINGS = 'header';
    // @editorjs/nested-list
    public const LISTS = 'list';
    // @editorjs/delimiter
    public const DELIMITERS = 'delimiter';
    // @editorjs/quote
    public const QUOTES = 'quote';
    // @editorjs/warning
    public const WARNINGS = 'warning';
    // @editorjs/image
    public const IMAGES = 'image';
    // @editorjs/embed
    public const EMBEDS = 'embed';
    // @editorjs/table
    public const TABLES = 'table';
    // @editorjs/attaches
    public const ATTACHMENTS = 'attaches';
    // @editorjs/raw
    public const RAW_CODE = 'raw';

    private const AVALIABLE_TOOLS = [
        self::PARAGRAPHS,
        self::HEADINGS,
        self::LISTS,
        self::DELIMITERS,
        self::QUOTES,
        self::WARNINGS,
        //self::IMAGES,
        //self::EMBEDS,
        self::TABLES,
        self::ATTACHMENTS,
        self::RAW_CODE,
    ];

    /**
     * @var array<string, OptionsResolver>
     */
    private static array $optionsResolvers = [];

    /**
     * @param string[] $enabledTools
     * @param string[] $disabledTools
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
                'EditorJs values cannot define both "enabledTools" and "disabledTools", please use one of them.'
            );
        }

        // Enable all tools by default
        if (empty($this->enabledTools)) {
            $this->enabledTools = self::AVALIABLE_TOOLS;
        } else {
            if (! empty($unknownTools = array_diff($this->enabledTools, self::AVALIABLE_TOOLS))) {
                throw new InvalidConfigurationException(
                    'Invalid EditorJs configuration: unknown tool(s): "' . implode('", "', $unknownTools) . '" (enabled).'
                );
            }
        }

        if (! empty($unknownTools = array_diff($this->disabledTools, self::AVALIABLE_TOOLS))) {
            throw new InvalidConfigurationException(
                'Invalid EditorJs configuration: unknown tool(s): "' . implode('", "', $unknownTools) . '" (disabled).'
            );
        }

        // Remove disabled tools (if any)
        foreach ($this->disabledTools as $disabledTool) {
            if (($key = array_search($disabledTool, $this->enabledTools, true)) !== false) {
                unset($this->enabledTools[$key]);
            }
        }

        $this->buildOptionResolvers();
        $this->resolveOptions();
    }

    private function buildOptionResolvers():void
    {
        if (! empty(self::$optionsResolvers)) {
            return;
        }

        $associativeArrayOfScalarValuesValidator = Validation::createIsValidCallable(
            new AssociativeArrayOfScalarValues(),
        );

        foreach (self::AVALIABLE_TOOLS as $tool) {
            $optionsResolver = new OptionsResolver();

            switch ($tool) {
                case self::PARAGRAPHS:
                    // No config params...

                    break;
                case self::HEADINGS:
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
                case self::LISTS:
                    // see https://github.com/editor-js/nested-list#config-params
                    // No config params...

                    break;
                case self::DELIMITERS:
                    // see https://github.com/editor-js/delimiter#config-params
                    // No config params...

                    break;
                case self::QUOTES:
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
                case self::WARNINGS:
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
                case self::IMAGES:
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
                case self::EMBEDS:
                    // see https://github.com/editor-js/embed#available-configuration
                    // No config params...

                    break;
                case self::TABLES:
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
                case self::ATTACHMENTS:
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
                case self::RAW_CODE:
                    // see https://github.com/editor-js/raw#config-params
                    $optionsResolver->define('placeholder')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                    ;

                    break;
            }

            self::$optionsResolvers[$tool] = $optionsResolver;
        }
    }

    private function resolveOptions()
    {
        $optionsResolver = new OptionsResolver();

        foreach ($this->enabledTools as $enabledTool) {
            $optionsResolver->define($enabledTool)
                ->default([])
                ->allowedTypes('array');
        }

        try {
            $this->options = $optionsResolver->resolve($this->options);
        } catch (UndefinedOptionsException $e) {
            throw new InvalidConfigurationException(
                'Invalid EditorJs configuration: ' . $e->getMessage() . ' Did you forget to enable the tool(s)?'
            );
        }

        foreach($this->options as $toolName => $toolOptions) {
            try {
                $this->options[$toolName] = self::$optionsResolvers[$toolName]->resolve($toolOptions);
            } catch (InvalidOptionsException $e) {
                throw new InvalidConfigurationException(
                    'Invalid EditorJs tool configuration (' . $toolName . '): ' . $e->getMessage()
                );
            }
        }
    }
}
