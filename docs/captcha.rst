Captcha
=======

`HCaptcha`_ is the default captcha system for this bundle. It creates ``meteo_concept_h_captcha.yaml`` in the configuration's files and it adds lines in ``.env``.

Form Type
----------

Simply use ``HCaptchaType`` where needed::

    // src/Form/ContactFormType.php

    //...
    use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;

    class ContactFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $formBuilder, array $options): void
        {
            $formBuilder
                ->add('full_name')
                ->add('email', EmailType::class)
                ->add('message', TextareaType::class)
                ->add('privacy_checkbox', CheckboxType::class)
                ->add('recaptcha', HCaptchaType::class)
            ;
        }

        // ...
    }

.. _`HCaptcha`: https://github.com/Meteo-Concept/hcaptcha-bundle