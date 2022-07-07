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

use LogicException;
use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Attaches;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Image;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class AdminEditorJsType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var string
     */
    public const TOOLS_OPTIONS = 'tools_options';

    /**
     * @var string
     */
    public const ENABLED_TOOLS = 'enabled_tools';

    /**
     * @var string
     */
    public const CSRF_TOKEN_ID_IMAGES = self::CSRF_TOKEN_ID.'_images';

    /**
     * @var string
     */
    public const CSRF_TOKEN_ID_ATTACHMENTS = self::CSRF_TOKEN_ID.'_attachments';

    /**
     * @var string
     */
    public const CSRF_TOKEN_ID = 'mwt_admin_editorjs_upload_api';

    public function __construct(
        private readonly AttachmentsAdminApiUrlGenerator $attachmentsAdminApiUrlGenerator,
        private readonly SerializerInterface $serializer,
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
        // Normalize tool options for EditorJs
        $formView->vars['tools_options'] = [];

        /** @var array<string, class-string> $enabledTools */
        $enabledTools = $options[self::ENABLED_TOOLS];
        /** @var array<string, array<string, mixed>> $toolsOptions */
        $toolsOptions = $options[self::TOOLS_OPTIONS];

        foreach ($enabledTools as $enabledTool) {
            if (isset($toolsOptions[$enabledTool])) {
                if (Image::class === $enabledTool) {
                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['captionPlaceholder'] = $toolsOptions[Image::class]['captionPlaceholder'];

                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['buttonContent'] = $toolsOptions[Image::class]['buttonContent'];

                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['api_token'] = $this->csrfTokenManager
                        ->getToken(self::CSRF_TOKEN_ID_IMAGES)
                        ->getValue()
                    ;

                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['endpoint'] = $this->attachmentsAdminApiUrlGenerator->generate(
                        [
                            'csrf_token_id' => self::CSRF_TOKEN_ID_IMAGES,
                            AdminAttachmentType::CONTEXT => (string) (new AttachmentAssociationContextDto(
                                Image::class,
                                'attachment',
                            )),
                            AdminAttachmentType::MAX_SIZE => $toolsOptions[Image::class]['maxSize'],
                            AdminAttachmentType::ALLOWED_MIME_TYPES => ['/image\/.+/'],
                            AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                            AdminAttachmentType::METADATA => [],
                            AdminAttachmentType::PROCESSORS_OPTIONS => $toolsOptions[Image::class]['processorsOptions'],
                        ],
                    );

                    continue;
                }

                // TODO: Implement attaches block (EditorJs)
                if (Attaches::class === $enabledTool) {
                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Attaches::class,
                    )]['api_token'] = $this->csrfTokenManager
                        ->getToken(self::CSRF_TOKEN_ID_ATTACHMENTS)
                        ->getValue()
                    ;

                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Attaches::class,
                    )]['endpoint'] = $this->attachmentsAdminApiUrlGenerator->generate(
                        [
                            'csrf_token_id' => self::CSRF_TOKEN_ID_ATTACHMENTS,
                            AdminAttachmentType::CONTEXT => (string) (new AttachmentAssociationContextDto(
                                Attaches::class,
                                'attachment',
                            )),
                            AdminAttachmentType::MAX_SIZE => $toolsOptions[Attaches::class]['maxSize'],
                            AdminAttachmentType::ALLOWED_MIME_TYPES => [],
                            AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                            AdminAttachmentType::METADATA => [],
                            AdminAttachmentType::PROCESSORS_OPTIONS => $toolsOptions[Attaches::class]['processorsOptions'],
                        ],
                    );

                    continue;
                }

                $formView->vars['tools_options'][Block::getTypeByClass(
                    $enabledTool,
                )] = $toolsOptions[$enabledTool];

                continue;
            }

            $formView->vars['tools_options'][Block::getTypeByClass($enabledTool)] = [];
        }
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'compound' => false,
            'constraints' => [new Valid()],
            self::TOOLS_OPTIONS => [],
            'is_empty_callback' => static function (EditorJsContent $value): bool {
                return $value->getBlocks()->isEmpty();
            },
        ]);

        $optionsResolver->setRequired([self::ENABLED_TOOLS]);

        $optionsResolver->setAllowedTypes(self::TOOLS_OPTIONS, 'array');
        $optionsResolver->setAllowedTypes(self::ENABLED_TOOLS, ['array']);
    }

    public function getBlockPrefix(): string
    {
        return 'mwt_admin_editorjs';
    }

    /**
     * @param ?EditorJsContent $data
     */
    public function transform($data): ?EditorJsContent
    {
        return $data;
    }

    public function reverseTransform($data): ?EditorJsContent
    {
        if (empty($data)) {
            return null;
        }

        if ($data instanceof EditorJsContent) {
            return $data;
        }

        $deserializedData = $this->serializer->deserialize($data, EditorJsContent::class, 'json');

        if (! $deserializedData instanceof EditorJsContent) {
            throw new LogicException('Data is not of the correct type.');
        }

        return $deserializedData;
    }
}
