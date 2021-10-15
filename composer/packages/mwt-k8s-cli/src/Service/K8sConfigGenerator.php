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

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class K8sConfigGenerator
{
    public function generateConfigFile(
        string $path,
        string $certificate,
        string $url,
        string $token,
        ?string $namespace = null,
    ): void {
        yaml_emit_file($path, $this->generateConfigArray($certificate, $url, $token, $namespace));
    }

    /**
     * @return array<string, mixed>
     */
    private function generateConfigArray(
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
                        'user' => 'default-user',
                    ],
                ],
            ],
            'users' => [
                [
                    'name' => 'default-user',
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
