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

namespace Mep\WebToolkitBundle\Router;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentsAdminApiUrlGenerator
{
    public function __construct(
        private AdminContextProvider $adminContextProvider,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    /**
     * @param array<string, mixed> $routeParams
     */
    public function generate(array $routeParams, ?string $crudControllerFqcn = null): string
    {
        if ($crudControllerFqcn === null) {
            $crudControllerFqcn = $this->adminContextProvider
                ?->getContext()
                ?->getCrud()
                ?->getControllerFqcn();
        }

        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController($crudControllerFqcn)
            ->setAction(AbstractCrudController::ACTION_ATTACH_FILE)
            ->set(EA::ROUTE_PARAMS, $routeParams)
            ->generateUrl();
    }
}
