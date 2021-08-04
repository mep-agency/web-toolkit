Authorization
=============

Authorization uses standard Symfony user login, but with `passwordless`_ links.

To make the system work, you should follow the next steps.

Basic entity implementation
---------------------------

Implement User's entity and repository (and CRUD controller too, if you want to manage the entity in the back office).
Extend ``AbstractUser`` in the entity, like this::

    // src/Entity/User.php

    // ...
    use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;

    /**
     * @ORM\Entity(repositoryClass=UserRepository::class)
     */
    class User extends AbstractUser
    {
        // ...
    }

The ``AbstractUser`` simply adds "email" and "roles" properties, also implements ``getUserIdentifier()`` method, that returns the email.

Security controller
-------------------

Implement a controller that extends ``AbstractSecurityController`` and the two abstract methods::

    // src/Repository/SecurityController.php

    // ...
    use Mep\WebToolkitBundle\Contract\Controller\AbstractSecurityController;
    use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

    class SecurityController extends AbstractSecurityController
    {
        public function __construct(private UserRepository $userRepository)
        {
        }

        protected function configureLoginTemplateParameters(array $parameters): array
        {
            $parameters['page_title'] = 'Login';

            return $parameters;
        }

        protected function findUser(string $identifier): ?AbstractUser
        {
            return $this->userRepository->findOneBy(['email' => $identifier]);
        }

        protected function sendUrlToUser(AbstractUser $user, LoginLinkDetails $loginLinkDetails): Response
        {
            return new Response($loginLinkDetails->getUrl());
        }
    }

The abstract controller implements come routes (login and logout).

Configuration
-------------

Change ``providers`` and ``firewalls`` in ``security.yaml``, like so::

    // config/packages/security.yaml

    // ...

    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            login_link:
                check_route: login_check
                signature_properties: ['id']
                login_path: login
            logout:
                path: logout
            entry_point: App\Controller\SecurityController

    // ...

Optionally you can change also ``framework.yaml`` for better session handling::

    // config/packages/framework.yaml

    // ...

    session:
        handler_id: session.handler.native_file
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
        save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'

    // ...

.. _`passwordless`: https://symfony.com/doc/current/security/login_link.html