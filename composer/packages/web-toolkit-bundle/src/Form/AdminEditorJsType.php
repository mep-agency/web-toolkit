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

use Mep\WebToolkitBundle\Entity\EditorJs\Block;
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
    const TOOLS_OPTIONS = 'tools_options';

    const ENABLED_TOOLS = 'enabled_tools';

    const PROPERTY_PATH = AdminAttachmentType::PROPERTY_PATH;

    const CSRF_TOKEN_ID_IMAGES = self::CSRF_TOKEN_ID . '_images';

    const CSRF_TOKEN_ID_ATTACHMENTS = self::CSRF_TOKEN_ID . '_attachments';

    const CSRF_TOKEN_ID = 'mwt_admin_editorjs_upload_api';

    public function __construct(
        private AttachmentsAdminApiUrlGenerator $attachmentsAdminApiUrlGenerator,
        private SerializerInterface $serializer,
        private CsrfTokenManagerInterface $tokenManager,
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
        // Normalize tool options for EditorJs
        $view->vars['tools_options'] = [];

        foreach ($options[self::ENABLED_TOOLS] as $enabledTool) {
            if (isset($options[self::TOOLS_OPTIONS][$enabledTool])) {
                if ($enabledTool === Block\Image::class) {
                    $view->vars['tools_options'][Block::getTypeByClass(Block\Image::class)]['context'] = Block\Image::ATTACHMENTS_CONTEXT;

                    $view->vars['tools_options'][Block::getTypeByClass(Block\Image::class)]['captionPlaceholder'] = $options[self::TOOLS_OPTIONS][Block\Image::class]['captionPlaceholder'];

                    $view->vars['tools_options'][Block::getTypeByClass(Block\Image::class)]['buttonContent'] = $options[self::TOOLS_OPTIONS][Block\Image::class]['buttonContent'];

                    $view->vars['tools_options'][Block::getTypeByClass(Block\Image::class)]['api_token'] = $this->tokenManager
                        ->getToken(self::CSRF_TOKEN_ID_IMAGES)
                        ->getValue();

                    $view->vars['tools_options'][Block::getTypeByClass(Block\Image::class)]['endpoint'] = $this->attachmentsAdminApiUrlGenerator->generate([
                        'csrf_token_id' => self::CSRF_TOKEN_ID_IMAGES,
                        AdminAttachmentType::PROPERTY_PATH => $options[AdminAttachmentType::PROPERTY_PATH],
                        AdminAttachmentType::MAX_SIZE => $options[self::TOOLS_OPTIONS][Block\Image::class]['maxSize'],
                        AdminAttachmentType::ALLOWED_MIME_TYPES => ['/image\/.+/'],
                        AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                        AdminAttachmentType::METADATA => [],
                        AdminAttachmentType::PROCESSORS_OPTIONS => $options[self::TOOLS_OPTIONS][Block\Image::class]['processorsOptions'],
                    ]);

                    continue;
                }

                // TODO: Implement attaches block (EditorJs)
                if ($enabledTool === Block\Attaches::class) {
                    $view->vars['tools_options'][Block::getTypeByClass(Block\Attaches::class)]['api_token'] = $this->tokenManager
                        ->getToken(self::CSRF_TOKEN_ID_ATTACHMENTS)
                        ->getValue();

                    $view->vars['tools_options'][Block::getTypeByClass(Block\Attaches::class)]['endpoint'] = $this->attachmentsAdminApiUrlGenerator->generate([
                        'csrf_token_id' => self::CSRF_TOKEN_ID_ATTACHMENTS,
                        AdminAttachmentType::PROPERTY_PATH => $options[AdminAttachmentType::PROPERTY_PATH],
                        AdminAttachmentType::MAX_SIZE => $options[self::TOOLS_OPTIONS][Block\Attaches::class]['maxSize'],
                        AdminAttachmentType::ALLOWED_MIME_TYPES => [],
                        AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                        AdminAttachmentType::METADATA => [],
                        AdminAttachmentType::PROCESSORS_OPTIONS => $options[self::TOOLS_OPTIONS][Block\Attaches::class]['processorsOptions'],
                    ]);

                    continue;
                }

                $view->vars['tools_options'][Block::getTypeByClass($enabledTool)] = $options[self::TOOLS_OPTIONS][$enabledTool];

                continue;
            }

            $view->vars['tools_options'][Block::getTypeByClass($enabledTool)] = [];
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'compound' => false,
            'constraints' => [
                new Valid(),
            ],
            self::TOOLS_OPTIONS => [],
        ]);

        $resolver->setRequired([
            self::ENABLED_TOOLS,
            self::PROPERTY_PATH,
        ]);

        $resolver->setAllowedTypes(self::PROPERTY_PATH, 'string');
        $resolver->setAllowedTypes(self::TOOLS_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::ENABLED_TOOLS, ['array']);

        $resolver->addNormalizer(
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
