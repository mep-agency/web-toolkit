Mail System
===========

For sending emails use standard `Symfony's Mailer`_.

To make the system work, you should follow the next steps.

Controller
----------

Autowire ``TemplateRenderer`` and use its ``render()`` method with a new instance of a template identifier (``TwigTemplate`` or ``DummyTemplate``) as first argument.
This will return an ``Email`` object and therefore can be manipulated as usual::

    // src/Controller/MailController.php

    // ...
    use Mep\WebToolkitBundle\Mail\TemplateIdentifier\TwigTemplate;
    use Mep\WebToolkitBundle\Mail\TemplateRenderer;
    use Symfony\Component\Mailer\MailerInterface;

    class MailController extends AbstractController
    {
        #[Route('/mail-sender', name: 'mail_sender')]
        public function mailSender(MailerInterface $mailer, TemplateRenderer $templateRenderer)
        {
            $email = $templateRenderer->render(
                new TwigTemplate('mail'),
                ['name' => 'John', 'surname' => 'Doe']
            );
            $email->to(new Address('to@example.com', "To"));
            $email->from(new Address('from@example.com', "From"));

            $mailer->send($email);

            // ...
        }

        // ...
    }

The ``TemplateRenderer`` render method accepts as second argument an array of parameters.

Templates
---------

If you use ``TwigTemplate`` you have to add some twig templates:

- html_body.html.twig
- subject.html.twig
- text_body.html.twig

You can use the parameters passed in the Controller::

    // templates/mail/html_body.html.twig

    <h1>Hi {{ name }} {{ surname }}!</h1>

    <p>This is an email for you!</p>

    //..



Configuration
-------------

Change ``MAILER_DSN`` in ``.env``, like so::

    // .env

    // ...

    ###> symfony/mailer ###
    MAILER_DSN=null://null
    ###< symfony/mailer ###

    // ...

.. _`Symfony's Mailer`: https://symfony.com/doc/current/mailer.html