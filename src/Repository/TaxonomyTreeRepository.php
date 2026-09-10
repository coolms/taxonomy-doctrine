<?php

declare(strict_types=1);

namespace CoolMS\Taxonomy\Doctrine\Repository;

use CoolMS\Taxonomy\Entity\TaxonomyNode;
use CoolMS\Taxonomy\Entity\TaxonomyTree;
use CoolMS\Taxonomy\Entity\TaxonomyTreeInterface;
use CoolMS\Taxonomy\Repository\TaxonomyTreeRepositoryInterface;
use CoolMS\Core\Doctrine\Repository\DoctrineRepository;
use CoolMS\Rql\Doctrine\DoctrineRqlVisitor;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @extends DoctrineRepository<TaxonomyTree>
 */
final class TaxonomyTreeRepository extends DoctrineRepository implements TaxonomyTreeRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        ParameterBagInterface $parameterBag,
        DoctrineRqlVisitor $rqlVisitor,
    ) {
        parent::__construct($registry, TaxonomyTree::class, $parameterBag, $rqlVisitor);
    }

    public function findByCode(string $code): ?TaxonomyTreeInterface
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function countNodes(TaxonomyTreeInterface $tree): int
    {
        $count = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(n.id)')
            ->from(TaxonomyNode::class, 'n')
            ->where('n.tree = :tree')
            ->setParameter('tree', $tree)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }
}
