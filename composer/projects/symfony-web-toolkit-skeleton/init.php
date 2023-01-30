#!/usr/bin/env php
<?php

require_once __DIR__.'/vendor/autoload.php';

use phpseclib3\Crypt\RSA;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

function runCommandWithMessageOrFail(SymfonyStyle $symfonyStyle, string $message, string $command): bool
{
    $symfonyStyle->note($message);

    $process = Process::fromShellCommandline($command);
    $process->mustRun();

    return $process->isSuccessful();
}

function webpackEncore_removeUnusedFiles(SymfonyStyle $symfonyStyle): void
{
    $filesystem = new Filesystem();

    $symfonyStyle->note('Removing unused files from Webpack Encore...');

    $filesystem->remove(__DIR__.'/assets/app.js');
    $filesystem->remove(__DIR__.'/assets/styles/app.css');
    $filesystem->remove(__DIR__.'/assets/bootstrap.js');
    $filesystem->remove(__DIR__.'/assets/controllers');
}

function init_removeUnusedFiles(SymfonyStyle $symfonyStyle): void
{
    $filesystem = new Filesystem();
    $unusedFiles = [__DIR__.'/README.md'];

    $symfonyStyle->note('Removing some unused files...');

    $filesystem->remove($unusedFiles);
}

function init_removeInitFile(SymfonyStyle $symfonyStyle): void
{
    $filesystem = new Filesystem();

    $symfonyStyle->note('Removing this script...');

    $filesystem->remove(__FILE__);

    $symfonyStyle->note('Removing the init script from composer.json...');

    $composerFileContent = file_get_contents(__DIR__.'/composer.json');

    if (false === $composerFileContent) {
        throw new RuntimeException('Unable to read composer.json');
    }

    /** @var array<string, mixed> $composerArrayContent */
    $composerArrayContent = json_decode($composerFileContent, true);
    /** @var array<string, mixed> $composerScripts */
    $composerScripts = $composerArrayContent['scripts'];

    unset($composerScripts['post-create-project-cmd']);

    $composerJsonContent = json_encode($composerArrayContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (false === $composerJsonContent) {
        throw new RuntimeException('Unable to read composer.json');
    }

    file_put_contents(__DIR__.'/composer.json', $composerJsonContent);
}

function generateRsaPrivateKeyInDotEnv(SymfonyStyle $symfonyStyle): void
{
    $symfonyStyle->note('Generating RSA private key...');

    $rsaPrivateKey = RSA::createKey(2048);
    $base64RsaPrivateKey = base64_encode($rsaPrivateKey->toString('PKCS8'));
    $dotEnvFile = file_get_contents(__DIR__.'/.env');

    if (false === $dotEnvFile) {
        throw new RuntimeException('Unable to read .env');
    }

    $privateKeyPlaceholderRegex = '#MWT_PRIVACY_CONSENT_MANAGER_PRIVATE_KEY_PLACEHOLDER#';
    $dotEnvFile = preg_replace($privateKeyPlaceholderRegex, $base64RsaPrivateKey, $dotEnvFile,);

    file_put_contents(__DIR__.'/.env', $dotEnvFile);
}

$application = (new SingleCommandApplication())
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $io->title('MEP Web Toolkit - Initializing project');

        // Run tasks
        webpackEncore_removeUnusedFiles($io);
        generateRsaPrivateKeyInDotEnv($io);
        runCommandWithMessageOrFail(
            $io,
            'Creating development SQLite DB...',
            'php bin/console doctrine:schema:create -n',
        );
        runCommandWithMessageOrFail(
            $io,
            'Creating sessions table...',
            'php bin/console mwt:sessions:create-table -n --ignore-missing-pdo-session-handler',
        );
        runCommandWithMessageOrFail($io, 'Loading fixtures...', 'php bin/console doctrine:fixtures:load -n');
        runCommandWithMessageOrFail($io, 'Installing front end dependencies...', 'yarn');
        runCommandWithMessageOrFail($io, 'Building front end assets...', 'yarn build');
        init_removeUnusedFiles($io);
        init_removeInitFile($io);

        // Create Git repository
        runCommandWithMessageOrFail(
            $io,
            'Creating Git repository...',
            'git init && git add --all && git commit -m "Initial commit"',
        );

        $io->success('Your new project is ready!');
    })
    ->run()
;
