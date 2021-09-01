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

use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
use Mep\WebToolkitBundle\Validator\EditorJs;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $view->vars['tools_options'] = $options[self::TOOLS_OPTIONS];

        foreach ($options[self::ENABLED_TOOLS] as $enabledTool) {
            if (! isset($view->vars['tools_options'][$enabledTool])) {
                $view->vars['tools_options'][$enabledTool] = [];
            }
        }

        if (isset($view->vars['tools_options'][EditorJs::IMAGES])) {
            $view->vars['tools_options'][EditorJs::IMAGES]['api_token_id'] = self::CSRF_TOKEN_ID_IMAGES;

            $view->vars['tools_options'][EditorJs::IMAGES]['endpoints'] = [
                'byFile' => $this->attachmentsAdminApiUrlGenerator->generate([
                    'csrf_token_id' => self::CSRF_TOKEN_ID_IMAGES,
                    AdminAttachmentType::PROPERTY_PATH => $options[AdminAttachmentType::PROPERTY_PATH],
                    AdminAttachmentType::MAX_SIZE => $view->vars['tools_options'][EditorJs::IMAGES]['maxSize'],
                    AdminAttachmentType::ALLOWED_MIME_TYPES => ['/image\/.+/'],
                    AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                    AdminAttachmentType::METADATA => [],
                    AdminAttachmentType::PROCESSORS_OPTIONS => $view->vars['tools_options'][EditorJs::IMAGES]['processorsOptions'],
                ]),
            ];

            // Unset options which are not used by JS
            unset($view->vars['tools_options'][EditorJs::IMAGES]['processorsOptions']);
        }

        if (isset($view->vars['tools_options'][EditorJs::ATTACHMENTS])) {
            $view->vars['tools_options'][EditorJs::ATTACHMENTS]['api_token_id'] = self::CSRF_TOKEN_ID_ATTACHMENTS;

            $view->vars['tools_options'][EditorJs::ATTACHMENTS]['endpoint'] = $this->attachmentsAdminApiUrlGenerator->generate([
                'csrf_token_id' => self::CSRF_TOKEN_ID_ATTACHMENTS,
                AdminAttachmentType::PROPERTY_PATH => $options[AdminAttachmentType::PROPERTY_PATH],
                AdminAttachmentType::MAX_SIZE => $view->vars['tools_options'][EditorJs::ATTACHMENTS]['maxSize'],
                AdminAttachmentType::ALLOWED_MIME_TYPES => [],
                AdminAttachmentType::ALLOWED_NAME_PATTERN => null,
                AdminAttachmentType::METADATA => [],
                AdminAttachmentType::PROCESSORS_OPTIONS => $view->vars['tools_options'][EditorJs::ATTACHMENTS]['processorsOptions'],
            ]);

            // Unset options which are not used by JS
            unset($view->vars['tools_options'][EditorJs::ATTACHMENTS]['processorsOptions']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'compound' => false,
            self::TOOLS_OPTIONS => [],
        ]);

        $resolver->setRequired([
            self::ENABLED_TOOLS,
            self::PROPERTY_PATH,
        ]);

        $resolver->setAllowedTypes(self::PROPERTY_PATH, 'string');
        $resolver->setAllowedTypes(self::TOOLS_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::ENABLED_TOOLS, ['array']);
    }

    public function getBlockPrefix(): string
    {
        return 'mwt_admin_editorjs';
    }

    public function transform($data): ?array
    {
        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function reverseTransform($data): ?array {
        if ($data === null || is_array($data)) {
            return $data;
        }

        try {
            return Json::decode($data, JSON::FORCE_ARRAY);
        } catch (JsonException $e) {
            throw new TransformationFailedException('Invalid EditorJs value: ' . $e->getMessage());
        }
    }
}
