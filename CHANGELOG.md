# Changelog

All notable changes to `coolms/taxonomy-doctrine` are recorded here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## 2.0.0-alpha1 - 2026-09-10

**A pre-release. It carries no compatibility promise.**

### Added

- `src/mapping/TaxonomyNode.orm.xml` and `src/mapping/TaxonomyTree.orm.xml` -
  the ORM mapping for the two entities in `coolms/taxonomy`, which carries no
  Doctrine attributes of its own.
- `Repository\TaxonomyNodeRepository` and `Repository\TaxonomyTreeRepository`,
  implementing the domain contracts on top of
  `CoolMS\Core\Doctrine\Repository\DoctrineRepository`.

### The mapping was written against resolved metadata, not transcribed

Five columns are deliberately absent from both files - `id`, `label`,
`createdAt`, `updatedAt`, `accessedAt` - because they arrive from traits whose
`CoolMS\Core\Mapping` attributes `TraitMappingDriver` translates earlier in the
driver chain. The class does not show them at all, so a file transcribed from
the class would either miss them or re-declare them; re-declaring one makes it a
two-source field, which is how a trait-level change becomes a silent per-entity
divergence.

Verified by diffing the schema the mapping produces against the running
database: the estate's pre-existing divergence is unchanged at 136 statements,
of which **none touch the taxonomy tables**. The conversion introduced no drift.

### Association targets are interfaces, preserved verbatim

`tree`, `parent`, `children` and `nodes` name `*Interface` because the
attributes did, and `resolve_target_entities` rewrites them. Naming the concrete
classes would have been a behaviour change wearing the clothes of a format
conversion.

### Why `coolms/entity` is constrained to `^2.0.0-alpha3`

`CoolMS\Entity\Attribute\DiscriminatorValue` moved into `coolms/entity` and
first shipped in **v2.0.0-alpha3**. Earlier releases resolve cleanly and then
fail the moment anything reflects the attribute, because PHP resolves an
attribute class lazily: the entity autoloads, `getAttributes()` returns an entry,
and only `newInstance()` throws.

⚠️ So `^2.0` would have been wrong in the quiet way. It installs and breaks on
first boot, and no `class_exists` check finds it.

~~Until alpha3 existed this was expressed as
`"conflict": {"coolms/entity": "<=2.0.0-alpha2"}`~~ -- a constraint can only name
a release that exists, so the conflict stood in for the floor until the floor
could be written. It has been removed now that it can.
