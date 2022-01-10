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

namespace Mep\WebToolkitBundle\Form;

use Mep\WebToolkitBundle\Dto\AdminAttachmentUploadDto;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Mep\WebToolkitBundle\Validator\AttachmentUploadedFile;
use Nette\Utils\Json;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validation;

/**
 * @internal do not use this type directly, use the public types/fields instead
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class AdminAttachmentUploadApiType extends AdminAttachmentType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        /** @var int $maxSize */
        $maxSize = $options[self::MAX_SIZE];
        /** @var string[] $allowedMimeTypes */
        $allowedMimeTypes = $options[self::ALLOWED_MIME_TYPES];
        /** @var ?string $allowedNamePattern */
        $allowedNamePattern = $options[self::ALLOWED_NAME_PATTERN];
        /** @var array<string, bool|float|int|string> $metadata */
        $metadata = $options[self::METADATA];
        /** @var array<string, bool|float|int|string> $processorOptions */
        $processorOptions = $options[self::PROCESSORS_OPTIONS];

        $formBuilder
            ->add('file', FileType::class, [
                'constraints' => [
                    new AttachmentUploadedFile(
                        $maxSize,
                        $allowedMimeTypes,
                        $allowedNamePattern,
                        $metadata,
                        $processorOptions,
                    ),
                ],
            ])
        ;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function buildView(FormView $formView, FormInterface $form, array $options): void
    {
        throw new RuntimeException('This FormType is meant for back end processing only.');
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefault('data_class', AdminAttachmentUploadDto::class);
        $optionsResolver->setDefault(self::METADATA, '{}');
        $optionsResolver->setDefault(self::PROCESSORS_OPTIONS, '{}');

        $optionsResolver->setAllowedTypes(self::METADATA, 'string');
        $optionsResolver->setAllowedTypes(self::PROCESSORS_OPTIONS, 'string');

        $optionsResolver->setNormalizer(self::METADATA, function (Options $options, $value) {
            return Json::decode($value, Json::FORCE_ARRAY);
        });
        $optionsResolver->setNormalizer(self::PROCESSORS_OPTIONS, function (Options $options, $value) {
            return Json::decode($value, Json::FORCE_ARRAY);
        });

        $associativeArrayOfScalarValuesValidator = function ($value): bool {
            $value = Json::decode($value, Json::FORCE_ARRAY);

            $constraintViolationList = Validation::createValidator()->validate(
                $value,
                new AssociativeArrayOfScalarValues(),
            );

            return 0 === $constraintViolationList->count();
        };
        $optionsResolver->setAllowedValues(self::METADATA, $associativeArrayOfScalarValuesValidator);
        $optionsResolver->setAllowedValues(self::PROCESSORS_OPTIONS, $associativeArrayOfScalarValuesValidator);
    }

    public function getBlockPrefix(): string
    {
        // Remove prefix from fields since this is used as an API endpoint
        return '';
    }

    /**
     * @return class-string
     */
    public function getParent(): string
    {
        return FormType::class;
    }
}
