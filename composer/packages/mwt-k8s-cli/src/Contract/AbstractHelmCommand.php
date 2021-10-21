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

namespace Mep\MwtK8sCli\Contract;

use Mep\MwtK8sCli\Service\HelmAppsManager;
use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractHelmCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        protected HelmAppsManager $helmAppsManager,
    ) {
        parent::__construct($kubernetesCluster);
    }
}
