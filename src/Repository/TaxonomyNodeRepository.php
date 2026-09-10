<?php

declare(strict_types=1);

namespace CoolMS\Taxonomy\Doctrine\Repository;

use CoolMS\Taxonomy\Entity\TaxonomyNode;
use CoolMS\Taxonomy\Entity\TaxonomyNodeInterface;
use CoolMS\Taxonomy\Entity\TaxonomyTreeInterface;
use CoolMS\Taxonomy\Repository\TaxonomyNodeRepositoryInterface;
use CoolMS\Core\Doctrine\Repository\DoctrineRepository;
use CoolMS\Rql\Doctrine\DoctrineRqlVisitor;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @extends DoctrineRepository<TaxonomyNode>
 */
final class TaxonomyNodeRepository extends DoctrineRepository implements TaxonomyNodeRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        ParameterBagInterface $parameterBag,
        DoctrineRqlVisitor $rqlVisitor,
    ) {
        parent::__construct($registry, TaxonomyNode::class, $parameterBag, $rqlVisitor);
    }

    public function findBySlug(string $slug): ?TaxonomyNodeInterface
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findByTree(TaxonomyTreeInterface $tree): array
    {
        return $this->findBy(['tree' => $tree], ['lft' => 'ASC']);
    }
}
