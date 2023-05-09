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

use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class K8sConfigGenerator
{
    public function __construct(
        private readonly KubernetesCluster $kubernetesCluster,
    ) {
    }

    public function generateConfigFile(string $path, string $serviceAccountName, string $namespace): void
    {
        $k8sServiceAccount = $this->kubernetesCluster->getServiceAccountByName($serviceAccountName, $namespace);
        /** @var array<string, mixed> $secretData */
        $secretData = $this->kubernetesCluster
            ->getSecretByName($serviceAccountName.'-token', $namespace)
            ->getData(true)
        ;
        /** @var string $caCrt */
        $caCrt = $secretData['ca.crt'];
        /** @var string $token */
        $token = $secretData['token'];
        /** @var ?string $namespace */
        $namespace = $secretData['namespace'];

        // The library doesn't return the clean API URL, so we generate one with no path nor query parameters
        $url = trim($this->kubernetesCluster->getCallableUrl('', []), '?');

        $this->generateConfigFileFromData(
            $path,
            $k8sServiceAccount->getName() ?: 'default-user',
            base64_encode($caCrt),
            $url,
            $token,
            $namespace,
        );
    }

    public function generateConfigFileFromData(
        string $path,
        string $accountName,
        string $certificate,
        string $url,
        string $token,
        ?string $namespace = null,
    ): void {
        yaml_emit_file($path, $this->generateConfigArray($accountName, $certificate, $url, $token, $namespace));
    }

    /**
     * @return array<string, mixed>
     */
    private function generateConfigArray(
        string $accountName,
        string $certificate,
        string $url,
        string $token,
        ?string $namespace = null,
    ): array {
        $config = [
            'apiVersion' => 'v1',
            'kind' => 'Config',
            'current-context' => 'default-context',
            'clusters' => [
                [
                    'name' => 'default-cluster',
                    'cluster' => [
                        'certificate-authority-data' => $certificate,
                        'server' => $url,
                    ],
                ],
            ],
            'contexts' => [
                [
                    'name' => 'default-context',
                    'context' => [
                        'cluster' => 'default-cluster',
                        'user' => $accountName,
                    ],
                ],
            ],
            'users' => [
                [
                    'name' => $accountName,
                    'user' => [
                        'token' => $token,
                    ],
                ],
            ],
        ];

        if (null !== $namespace) {
            $config['contexts'][0]['context']['namespace'] = $namespace;
        }

        return $config;
    }
}
