<?php

declare(strict_types=1);

namespace App\Security;

class UserRole
{
    // Roles [START]

    /**
     * @var string
     */
    final public const ROLE_USER = 'ROLE_USER';

    /**
     * @var string
     */
    final public const ROLE_EDITOR = 'ROLE_EDITOR';

    /**
     * @var string
     */
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var array<string, string>
     */
    final public const ROLES = [
        'user.role.user' => self::ROLE_USER,
        'user.role.editor' => self::ROLE_EDITOR,
        'user.role.admin' => self::ROLE_ADMIN,
    ];

    // Roles [END]

    // Permissions [START]

    /**
     * @var string
     */
    final public const CAN_EDIT_ENTITY = 'CAN_EDIT_ENTITY';

    /**
     * @var string
     */
    final public const CAN_EDIT_USER_ENTITY = 'CAN_EDIT_USER_ENTITY';

    /**
     * @var string
     */
    final public const CAN_EDIT_PAGE_SETTINGS = 'CAN_EDIT_PAGE_SETTINGS';

    /**
     * @var string
     */
    final public const CAN_EDIT_PRIVACY_SETTINGS = 'CAN_EDIT_PRIVACY_SETTINGS';

    /**
     * @var array<string, string[]>
     */
    final public const PERMISSIONS_PER_ROLE = [
        self::ROLE_USER => [],
        self::ROLE_EDITOR => [self::CAN_EDIT_ENTITY],
    ];

    // Permissions [END]
}
