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

use InvalidArgumentException;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
use Mep\WebToolkitBundle\Repository\AttachmentRepository;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Nette\Utils\Json;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Validation;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class AdminAttachmentType extends AbstractType implements DataTransformerInterface
{
    public const PROPERTY_PATH = 'attachment_property_path';

    public const MAX_SIZE = 'max_size';

    public const ALLOWED_MIME_TYPES = 'allowed_mime_types';

    public const ALLOWED_NAME_PATTERN = 'allowed_name_pattern';

    public const METADATA = 'metadata';

    public const PROCESSORS_OPTIONS = 'processors_options';

    protected const CSRF_TOKEN_ID = 'mwt_admin_attachment_upload_api';

    public function __construct(
        private AttachmentRepository $attachmentRepository,
        private AttachmentsAdminApiUrlGenerator $attachmentsAdminApiUrlGenerator,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer($this)
            ->addViewTransformer($this)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['api_url'] = $this->attachmentsAdminApiUrlGenerator->generate([
            'csrf_token_id' => self::CSRF_TOKEN_ID,
            self::PROPERTY_PATH => $options[self::PROPERTY_PATH],
            self::MAX_SIZE => $options[self::MAX_SIZE],
            self::ALLOWED_MIME_TYPES => $options[self::ALLOWED_MIME_TYPES],
            self::ALLOWED_NAME_PATTERN => $options[self::ALLOWED_NAME_PATTERN],
            self::METADATA => Json::encode($options[self::METADATA]),
            self::PROCESSORS_OPTIONS => Json::encode($options[self::PROCESSORS_OPTIONS]),
        ]);
        $view->vars['api_token_id'] = self::CSRF_TOKEN_ID;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::MAX_SIZE => 0,
            self::ALLOWED_MIME_TYPES => [],
            self::ALLOWED_NAME_PATTERN => null,
            self::METADATA => [],
            self::PROCESSORS_OPTIONS => [],
        ]);

        $resolver->setRequired([
            self::PROPERTY_PATH,
        ]);

        $resolver->setAllowedTypes(self::PROPERTY_PATH, 'string');
        $resolver->setAllowedTypes(self::MAX_SIZE, ['int', 'string']);
        $resolver->setAllowedTypes(self::ALLOWED_MIME_TYPES, 'array');
        $resolver->setAllowedTypes(self::ALLOWED_NAME_PATTERN, ['string', 'null']);
        $resolver->setAllowedTypes(self::METADATA, 'array');
        $resolver->setAllowedTypes(self::PROCESSORS_OPTIONS, 'array');

        $resolver->setNormalizer(self::MAX_SIZE, function (Options $options, $value) {
            if (is_string($value)) {
                return intval($value);
            }

            return $value;
        });

        $resolver->setAllowedValues(self::MAX_SIZE, Validation::createIsValidCallable(
            new PositiveOrZero()
        ));
        $associativeArrayOfScalarValuesValidator = Validation::createIsValidCallable(
            new AssociativeArrayOfScalarValues(),
        );
        $resolver->setAllowedValues(self::METADATA, $associativeArrayOfScalarValuesValidator);
        $resolver->setAllowedValues(self::PROCESSORS_OPTIONS, $associativeArrayOfScalarValuesValidator);
    }

    public function getBlockPrefix(): string
    {
        return 'mwt_admin_attachment';
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function transform($data)
    {
        // Model data should not be transformed
        return $data;
    }

    public function reverseTransform($data) {
        if ($data === null || $data instanceof Attachment) {
            return $data;
        }

        if (is_string($data)) {
            try {
                $data = Uuid::fromString($data);
            } catch (InvalidArgumentException $e) {
                throw new TransformationFailedException($e->getMessage());
            }
        } else {
            throw new TransformationFailedException('Invalid attachment value.');
        }

        $result = $data ? $this->attachmentRepository->find($data) : null;

        if ($result === null) {
            throw new TransformationFailedException('Attachment not found: "' . $data . '".');
        }

        return $result;
    }
}
