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

namespace Mep\WebToolkitBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 *
 * @extends AbstractCrudController<PrivacyConsentCategory>
 */
#[IsGranted('ROLE_ADMIN')]
class PrivacyConsentCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PrivacyConsentCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort([
            'priority' => 'DESC',
        ]);
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('stringId'),
            TextField::new('name'),
            TextField::new('description'),
            BooleanField::new('required')
                ->setRequired(false),
            IntegerField::new('priority'),
        ];
    }
}
