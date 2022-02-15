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

namespace Mep\MepWebToolkitK8sCli\Service;

use Mep\MepWebToolkitK8sCli\Exception\StopExecutionException;
use Mep\MepWebToolkitK8sCli\K8sCli;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Command\Command;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class K8sPullSecretGenerator
{
    /**
     * @var string
     */
    public final const PULL_SECRET_TYPE = 'kubernetes.io/dockerconfigjson';

    /**
     * @var string[]
     */
    public final const PULL_SECRET_DATA_KEYS = ['.dockerconfigjson'];

    public function __construct(
        private readonly KubernetesCluster $kubernetesCluster,
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
            ->setType(self::PULL_SECRET_TYPE)
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

    public function isValidSecretOrStop(K8sResource $k8sResource): void
    {
        $isNotSecret = 'Secret' !== $k8sResource->getKind();
        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        $isNotOpaque = self::PULL_SECRET_TYPE !== $k8sResource->getType();
        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        $isNotValidFormat = self::PULL_SECRET_DATA_KEYS !== array_keys($k8sResource->getData(false));

        if ($isNotSecret || $isNotOpaque || $isNotValidFormat) {
            throw new StopExecutionException('Unexpected secret type, aborting...', Command::INVALID);
        }
    }
}
