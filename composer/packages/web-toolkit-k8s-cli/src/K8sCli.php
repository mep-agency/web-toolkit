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

namespace Mep\MepWebToolkitK8sCli;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class K8sCli
{
    /**
     * @var string
     */
    public const K8S_DEFAULT_NAMESPACE = 'mwt-apps';

    /**
     * @var string
     */
    public const K8S_CREATED_BY_LABEL_NAME = 'app.kubernetes.io/created-by';

    /**
     * @var string
     */
    public const K8S_CREATED_BY_LABEL_VALUE = 'web-toolkit-k8s-cli';

    /**
     * @var array<string, string>
     */
    public const K8S_MINIMUM_NEW_RESOURCE_LABELS = [
        self::K8S_CREATED_BY_LABEL_NAME => self::K8S_CREATED_BY_LABEL_VALUE,
    ];

    private ContainerBuilder $containerBuilder;

    public function __construct()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    public function init(): void
    {
        $this->containerBuilder->setParameter('working_dir', getcwd());

        // Autoconfigure CLI commands
        $this->containerBuilder->registerForAutoconfiguration(Command::class)
            ->addTag('console.command')
        ;

        // Load services
        $phpFileLoader = new PhpFileLoader($this->containerBuilder, new FileLocator(__DIR__.'/../config'));
        $phpFileLoader->load('services.php')
        ;

        $this->containerBuilder->compile();
    }

    public function run(): void
    {
        /** @var Application $app */
        $app = $this->containerBuilder->get('application');

        $app->run();
    }
}
