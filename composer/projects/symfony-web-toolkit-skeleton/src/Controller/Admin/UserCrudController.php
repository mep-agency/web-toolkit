<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted(UserRole::CAN_EDIT_USER_ENTITY)]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * @return FieldInterface[]
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email'),
            ChoiceField::new('roles')
                ->setChoices(UserRole::ROLES)
                ->setFormTypeOption('choice_attr', [
                    'user.role.user' => [
                        'disabled' => true,
                    ],
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
        ];
    }
}
