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
use Mep\WebToolkitBundle\Validator\ValidAttachmentFile;
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
 * @internal Do not use this type directly, use the public types/fields instead.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AdminAttachmentUploadApiType extends AdminAttachmentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'constraints' => [
                    new ValidAttachmentFile(
                        $options[self::MAX_SIZE],
                        $options[self::ALLOWED_MIME_TYPES],
                        $options[self::ALLOWED_NAME_PATTERN],
                        $options[self::METADATA],
                        $options[self::PROCESSORS_OPTIONS],
                    ),
                ],
            ])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        throw new RuntimeException('This FormType is meant for back end processing only.');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', AdminAttachmentUploadDto::class);
        $resolver->setDefault(self::METADATA, '{}');
        $resolver->setDefault(self::PROCESSORS_OPTIONS, '{}');

        $resolver->setAllowedTypes(self::METADATA, 'string');
        $resolver->setAllowedTypes(self::PROCESSORS_OPTIONS, 'string');

        $resolver->setNormalizer(self::METADATA, function (Options $options, $value) {
            return Json::decode($value, Json::FORCE_ARRAY);
        });
        $resolver->setNormalizer(self::PROCESSORS_OPTIONS, function (Options $options, $value) {
            return Json::decode($value, Json::FORCE_ARRAY);
        });

        $associativeArrayOfScalarValuesValidator = function ($value) {
            $value = Json::decode($value, Json::FORCE_ARRAY);

            $violations = Validation::createValidator()->validate($value, new AssociativeArrayOfScalarValues());

            return $violations->count() === 0;
        };
        $resolver->setAllowedValues(self::METADATA, $associativeArrayOfScalarValuesValidator);
        $resolver->setAllowedValues(self::PROCESSORS_OPTIONS, $associativeArrayOfScalarValuesValidator);
    }

    public function getBlockPrefix(): string
    {
        // Remove prefix from fields since this is used as an API endpoint
        return '';
    }

    public function getParent()
    {
        return FormType::class;
    }
}
