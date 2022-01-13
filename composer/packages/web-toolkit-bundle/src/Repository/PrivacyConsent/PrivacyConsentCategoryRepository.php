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
use Doctrine\Persistence\ManagerRegistry;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryInterface;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryTrait;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentCategory;

/**
 * @method null|PrivacyConsentCategory find($id, $lockMode = null, $lockVersion = null)
 * @method null|PrivacyConsentCategory findOneBy(array $criteria, array $orderBy = null)
 * @method PrivacyConsentCategory[]    findAll()
 * @method PrivacyConsentCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<PrivacyConsentCategory>
 *
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentCategoryRepository extends ServiceEntityRepository implements LocalizedRepositoryInterface
{
    use LocalizedRepositoryTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PrivacyConsentCategory::class);
    }

    /**
     * @return PrivacyConsentCategory[]
     */
    public function findAllOrderedByPriority(): array
    {
        return $this->findBy([], [
            'priority' => 'DESC',
        ]);
    }
}
