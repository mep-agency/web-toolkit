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

namespace Mep\WebToolkitBundle\Repository\PrivacyConsent;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsent;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PublicKey;

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
    /**
     * @var string
     */
    public const MAX_PRIVACY_CONSENT_PER_PAGE = '6';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PrivacyConsent::class);
    }

    public function findLatestByPublicKey(PublicKey $publicKey): ?PrivacyConsent
    {
        return $this->findOneBy([
            'userPublicKey' => $publicKey,
        ], [
            'id' => Criteria::DESC,
        ]);
    }

    /**
     * @return Paginator<PrivacyConsent>
     */
    public function findAllByToken(PublicKey $publicKey, int $itemsPerPage, int $offset = 0): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.userPublicKey = :userPublicKey')
            ->setParameter('userPublicKey', $publicKey)
            ->orderBy('id', Criteria::DESC)
            ->setFirstResult($offset)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
        ;

        return new Paginator($query);
    }
}
