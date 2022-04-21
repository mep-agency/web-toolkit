<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Iterator;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentCategory;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted(UserRole::ROLE_USER)]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin')]
    public function index(): Response
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin')
            ->setTranslationDomain('back-office')
        ;
    }

    /**
     * @return Iterator<MenuItemInterface>
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('menu.content');
        yield MenuItem::linkToCrud('menu.content.user', 'fas fa-user', User::class)
            ->setPermission(UserRole::CAN_EDIT_USER_ENTITY)
        ;
        yield MenuItem::section('menu.privacy_consent', 'fas fa-cookie-bite')
            ->setPermission(UserRole::CAN_EDIT_PRIVACY_SETTINGS)
        ;
        yield MenuItem::linkToCrud('menu.privacy_consent.category', 'fas fa-tag', PrivacyConsentCategory::class)
            ->setPermission(UserRole::CAN_EDIT_PRIVACY_SETTINGS)
        ;
        yield MenuItem::linkToCrud('menu.privacy_consent.service', 'fas fa-check-square', PrivacyConsentService::class)
            ->setPermission(UserRole::CAN_EDIT_PRIVACY_SETTINGS)
        ;
    }
}
