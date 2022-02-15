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

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
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
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Validation;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class AdminAttachmentType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var string
     */
    public final const CONTEXT = 'context';

    /**
     * @var string
     */
    public final const MAX_SIZE = 'max_size';

    /**
     * @var string
     */
    public final const ALLOWED_MIME_TYPES = 'allowed_mime_types';

    /**
     * @var string
     */
    public final const ALLOWED_NAME_PATTERN = 'allowed_name_pattern';

    /**
     * @var string
     */
    public final const METADATA = 'metadata';

    /**
     * @var string
     */
    public final const PROCESSORS_OPTIONS = 'processors_options';

    /**
     * @var string
     */
    protected const CSRF_TOKEN_ID = 'mwt_admin_attachment_upload_api';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttachmentsAdminApiUrlGenerator $attachmentsAdminApiUrlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder
            ->addModelTransformer($this)
            ->addViewTransformer($this)
        ;
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    public function buildView(FormView $formView, FormInterface $form, array $options): void
    {
        $formView->vars['api_url'] = $this->attachmentsAdminApiUrlGenerator->generate([
            'csrf_token_id' => self::CSRF_TOKEN_ID,
            self::CONTEXT => $options[self::CONTEXT],
            self::MAX_SIZE => $options[self::MAX_SIZE],
            self::ALLOWED_MIME_TYPES => $options[self::ALLOWED_MIME_TYPES],
            self::ALLOWED_NAME_PATTERN => $options[self::ALLOWED_NAME_PATTERN],
            self::METADATA => Json::encode($options[self::METADATA]),
            self::PROCESSORS_OPTIONS => Json::encode($options[self::PROCESSORS_OPTIONS]),
        ]);

        $formView->vars['api_token'] = $this->csrfTokenManager
            ->getToken(self::CSRF_TOKEN_ID)
            ->getValue()
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            self::MAX_SIZE => 0,
            self::ALLOWED_MIME_TYPES => [],
            self::ALLOWED_NAME_PATTERN => null,
            self::METADATA => [],
            self::PROCESSORS_OPTIONS => [],
        ]);

        $optionsResolver->setRequired([self::CONTEXT]);

        $optionsResolver->setAllowedTypes(self::CONTEXT, 'string');
        $optionsResolver->setAllowedTypes(self::MAX_SIZE, ['int', 'string']);
        $optionsResolver->setAllowedTypes(self::ALLOWED_MIME_TYPES, 'array');
        $optionsResolver->setAllowedTypes(self::ALLOWED_NAME_PATTERN, ['string', 'null']);
        $optionsResolver->setAllowedTypes(self::METADATA, 'array');
        $optionsResolver->setAllowedTypes(self::PROCESSORS_OPTIONS, 'array');

        $optionsResolver->setNormalizer(self::MAX_SIZE, function (Options $options, $value) {
            if (is_string($value)) {
                return (int) $value;
            }

            return $value;
        });

        $optionsResolver->setAllowedValues(self::MAX_SIZE, Validation::createIsValidCallable(new PositiveOrZero()));

        $associativeArrayOfScalarValuesValidator = Validation::createIsValidCallable(
            new AssociativeArrayOfScalarValues(),
        );
        $optionsResolver->setAllowedValues(self::METADATA, $associativeArrayOfScalarValuesValidator);
        $optionsResolver->setAllowedValues(self::PROCESSORS_OPTIONS, $associativeArrayOfScalarValuesValidator);
    }

    public function getBlockPrefix(): string
    {
        return 'mwt_admin_attachment';
    }

    /**
     * @return class-string<TextType>
     */
    public function getParent()
    {
        return TextType::class;
    }

    public function transform($data)
    {
        // Model data should not be transformed
        return $data;
    }

    public function reverseTransform($data): ?Attachment
    {
        if (null === $data || $data instanceof Attachment) {
            return $data;
        }

        if (is_string($data)) {
            try {
                $data = Uuid::fromString($data);
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw new TransformationFailedException($invalidArgumentException->getMessage());
            }
        } else {
            throw new TransformationFailedException('Invalid attachment value.');
        }

        $attachment = $this->entityManager
            ->getRepository(Attachment::class)
            ->find($data)
        ;

        if (! $attachment instanceof Attachment) {
            throw new TransformationFailedException('Attachment not found: "'.$data.'".');
        }

        return $attachment;
    }
}
