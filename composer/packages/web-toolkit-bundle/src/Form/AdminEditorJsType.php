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

use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Attaches;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Image;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
use Mep\WebToolkitBundle\Validator\EditorJs\EditorJsNotEmpty;
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
        private AttachmentsAdminApiUrlGenerator $attachmentsAdminApiUrlGenerator,
        private SerializerInterface $serializer,
        private CsrfTokenManagerInterface $csrfTokenManager,
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

        foreach ($options[self::ENABLED_TOOLS] as $enabledTool) {
            if (isset($options[self::TOOLS_OPTIONS][$enabledTool])) {
                if (Image::class === $enabledTool) {
                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['captionPlaceholder'] = $options[self::TOOLS_OPTIONS][Image::class]['captionPlaceholder'];

                    $formView->vars['tools_options'][Block::getTypeByClass(
                        Image::class,
                    )]['buttonContent'] = $options[self::TOOLS_OPTIONS][Image::class]['buttonContent'];

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
                            AdminAttachmentType::MAX_SIZE => $options[self::TOOLS_OPTIONS][Image::class]['maxSize'],
                            AdminAttachmentType::ALLOWED_MIME_TYPES => ['/image\/.+/'],
                            AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                            AdminAttachmentType::METADATA => [],
                            AdminAttachmentType::PROCESSORS_OPTIONS => $options[self::TOOLS_OPTIONS][Image::class]['processorsOptions'],
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
                            AdminAttachmentType::MAX_SIZE => $options[self::TOOLS_OPTIONS][Attaches::class]['maxSize'],
                            AdminAttachmentType::ALLOWED_MIME_TYPES => [],
                            AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                            AdminAttachmentType::METADATA => [],
                            AdminAttachmentType::PROCESSORS_OPTIONS => $options[self::TOOLS_OPTIONS][Attaches::class]['processorsOptions'],
                        ],
                    );

                    continue;
                }

                $formView->vars['tools_options'][Block::getTypeByClass(
                    $enabledTool,
                )] = $options[self::TOOLS_OPTIONS][$enabledTool];

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
        ]);

        $optionsResolver->setRequired([self::ENABLED_TOOLS]);

        $optionsResolver->setAllowedTypes(self::TOOLS_OPTIONS, 'array');
        $optionsResolver->setAllowedTypes(self::ENABLED_TOOLS, ['array']);

        $optionsResolver->addNormalizer(
            'constraints',
            function (Options $options, $value): mixed {
                if ($options->offsetGet('required')) {
                    $value[] = new EditorJsNotEmpty();
                }

                return $value;
            },
        );
    }

    public function getBlockPrefix(): string
    {
        return 'mwt_admin_editorjs';
    }

    public function transform($data): ?EditorJsContent
    {
        return $data;
    }

    public function reverseTransform($data): ?EditorJsContent
    {
        if (empty($data) || $data instanceof EditorJsContent) {
            return $data;
        }

        return $this->serializer->deserialize($data, EditorJsContent::class, 'json');
    }
}
