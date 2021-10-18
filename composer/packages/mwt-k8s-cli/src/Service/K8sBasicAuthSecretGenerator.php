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

use Mep\MwtK8sCli\Exception\StopExecutionException;
use Mep\MwtK8sCli\K8sCli;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\Kinds\K8sSecret;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Command\Command;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class K8sBasicAuthSecretGenerator
{
    /**
     * @var string
     */
    private const BASIC_AUTH_SECRET_TYPE = 'Opaque';

    /**
     * @var string[]
     */
    private const BASIC_AUTH_SECRET_DATA_KEYS = ['auth'];

    public function __construct(
        private KubernetesCluster $kubernetesCluster,
    ) {
    }

    public function generate(string $name, string $username, string $password, string $namespace,): K8sSecret
    {
        return $this->kubernetesCluster
            ->secret()
            ->setName($name)
            ->setNamespace($namespace)
            ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
            ->setData([
                'auth' => $username.':'.password_hash($password, PASSWORD_BCRYPT),
            ])
        ;
    }

    public function isValidSecretOrStop(K8sResource $k8sResource): void
    {
        $isNotSecret = 'Secret' !== $k8sResource->getKind();
        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        $isNotOpaque = self::BASIC_AUTH_SECRET_TYPE !== $k8sResource->getType();
        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        $isNotValidFormat = self::BASIC_AUTH_SECRET_DATA_KEYS !== array_keys($k8sResource->getData(false));

        if ($isNotSecret || $isNotOpaque || $isNotValidFormat) {
            throw new StopExecutionException('Unexpected secret type, aborting...', Command::INVALID);
        }
    }
}
