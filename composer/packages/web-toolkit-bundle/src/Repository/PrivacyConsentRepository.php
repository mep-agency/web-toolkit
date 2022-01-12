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

namespace Mep\WebToolkitBundle\Repository;

use Mep\WebToolkitBundle\Entity\PrivacyConsent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|PrivacyConsent find($id, $lockMode = null, $lockVersion = null)
 * @method null|PrivacyConsent findOneBy(array $criteria, array $orderBy = null)
 * @method PrivacyConsent[]    findAll()
 * @method PrivacyConsent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<PrivacyConsent>
 *
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PrivacyConsent::class);
    }

    public function findLastByToken(string $token): ?PrivacyConsent
    {
        return $this->findOneBy(
            ['token' => $token],
            ['datetime' => 'DESC'],
        );
    }
}
