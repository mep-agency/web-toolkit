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

namespace Mep\MwtK8sCli\Service;

use Mep\MwtK8sCli\K8sCli;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class K8sPullSecretGenerator
{
    public function __construct(
        private KubernetesCluster $kubernetesCluster,
    ) {
    }

    public function generate(
        string $name,
        string $registry,
        string $username,
        string $password,
        string $namespace,
    ): K8sSecret {
        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        return $this->kubernetesCluster
            ->secret()
            ->setType('kubernetes.io/dockerconfigjson')
            ->setName($name)
            ->setNamespace($namespace)
            ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
            ->setData([
                '.dockerconfigjson' => json_encode([
                    'auths' => [
                        $registry => [
                            'username' => $username,
                            'password' => $password,
                            'auth' => base64_encode($username.':'.$password),
                        ],
                    ],
                ]),
            ])
        ;
    }
}
