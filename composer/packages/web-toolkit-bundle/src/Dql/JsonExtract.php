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

namespace Mep\WebToolkitBundle\Dql;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\Query\SqlWalker;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonExtract as MySqlJsonExtract;

/**
 * JSON_EXTRACT is supported by both MySql and Sqlite platforms with a compatible implementation.
 * We are extending the platform validation to avoid registering different functions depending on
 * the platform.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
class JsonExtract extends MySqlJsonExtract
{
    protected function validatePlatform(SqlWalker $sqlWalker): void
    {
        $isMySql = $sqlWalker->getConnection()->getDatabasePlatform() instanceof MySqlPlatform;
        $isSqlite = $sqlWalker->getConnection()->getDatabasePlatform() instanceof SqlitePlatform;

        if (! ($isMySql || $isSqlite)) {
            throw Exception::notSupported(static::FUNCTION_NAME);
        }
    }
}
