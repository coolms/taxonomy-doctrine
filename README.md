# coolms/taxonomy-doctrine

The Doctrine half of the taxonomy family: XML mapping for the two entities, and
the repository implementations behind the domain's contracts.

```bash
composer require coolms/taxonomy-doctrine
```

## Why the mapping is XML

`coolms/taxonomy` must not import the ORM, so the mapping cannot live beside the
entities. It ships here instead, as `src/mapping/*.orm.xml`, and a consuming
application points Doctrine at it:

```php
$container->prependExtensionConfig('doctrine', [
    'orm' => ['entity_managers' => ['default' => ['mappings' => [
        'Taxonomy' => [
            'is_bundle' => false,
            'type' => 'xml',
            'dir' => '%kernel.project_dir%/vendor/coolms/taxonomy-doctrine/src/mapping',
            'prefix' => 'CoolMS\Taxonomy\Entity',
        ],
    ]]]],
]);
```

⚠️ **`is_bundle` must be false.** With it true, `dir` resolves against the
bundle directory rather than the path you wrote, and the failure is a
non-existent-directory error at cache build - after the autoloader has happily
reported every class as loadable. Loading is not mapping.

`coolms/taxonomy-bundle` does this for you.

## What the mapping declares, and what it does not

It declares each entity's **own** columns and associations. `id`, `label`,
`createdAt`, `updatedAt` and `accessedAt` are deliberately **absent**: they come
from `IdentifierProviderTrait`, `LabelProviderTrait` and `TimestampableTrait`,
whose `CoolMS\Core\Mapping` attributes `TraitMappingDriver` translates earlier in
the driver chain.

⚠️ **Do not re-declare a trait column here.** The trait is its single source of
truth, and a second declaration makes a trait-level change a silent per-entity
divergence. In the sibling field package that mistake dropped a column default
and surfaced as one line of schema drift.

If you add an entity under `CoolMS\Taxonomy\Entity` with no matching
`.orm.xml`, the driver simply does not map it and **nothing reports it** - the
class just never becomes an entity. Add the mapping in the same change as the
class.

## The discriminator map names only the parent

`TaxonomyNode.orm.xml` maps `taxonomy_node` to `TaxonomyNode` and nothing else.
Subclasses register themselves through `#[DiscriminatorValue]`; see
`coolms/taxonomy`. Adding a consumer's class to this file makes the package
unpublishable.

## Repositories

`Repository\TaxonomyNodeRepository` and `Repository\TaxonomyTreeRepository`
extend `CoolMS\Core\Doctrine\Repository\DoctrineRepository` and implement the
domain interfaces. They are not auto-discovered by a consuming application's
service scan - `coolms/taxonomy-bundle` registers and aliases them.

## Requires

`coolms/core`, `coolms/core-doctrine`, `coolms/entity`, `coolms/taxonomy`,
`coolms/rql`, `coolms/rql-doctrine` (`^1.0.1` - the namespace moved inside
`^1.0`), `doctrine/orm`, `doctrine/persistence`,
`symfony/dependency-injection`.
