#!/usr/bin/env php
<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Filesystem\Filesystem;

function webpackEncore_removeUnusedFiles(): void
{
    $filesystem = new Filesystem();

    $filesystem->remove(__DIR__.'/assets/app.js');
    $filesystem->remove(__DIR__.'/assets/styles/app.css');
}

// Run tasks
webpackEncore_removeUnusedFiles();

// Remove self
$filesystem = new Filesystem();
$filesystem->remove(__FILE__);
