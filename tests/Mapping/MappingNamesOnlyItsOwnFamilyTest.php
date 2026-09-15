<?php

declare(strict_types=1);

namespace CoolMS\Taxonomy\Doctrine\Tests\Mapping;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function dirname;
use function glob;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function str_starts_with;

/**
 * The mapping files name classes, and every class they name must exist and
 * belong to this family. Two rules the files state in their own comments and
 * nothing else enforced:
 *
 *  - the mapped entity and every discriminator entry are classes of
 *    `CoolMS\Taxonomy\`, the domain package this adapter maps -- a map that
 *    named a consumer's subclass would make this package depend on the
 *    application that installs it;
 *  - the discriminator map names ONLY the root. A subclass registers itself
 *    through `#[DiscriminatorValue]` at load time, so a second entry here is
 *    a class the root was modified to know about, which is the change the
 *    rule forbids.
 *
 * Read from the XML rather than through the ORM's driver, so a wrong class
 * name is reported as a wrong name and not as a metadata exception three
 * frames down.
 */
final class MappingNamesOnlyItsOwnFamilyTest extends TestCase
{
    private const string FAMILY = 'CoolMS\\Taxonomy\\';
    private const string XML_NS = 'http://doctrine-project.org/schemas/orm/doctrine-mapping';

    /** @return iterable<string, array{string}> */
    public static function mappingFiles(): iterable
    {
        $files = glob(dirname(__DIR__, 2) . '/src/mapping/*.orm.xml') ?: [];
        self::assertNotSame([], $files, 'no mapping files found -- the glob is wrong or the directory moved');
        foreach ($files as $file) {
            yield basename($file) => [$file];
        }
    }

    #[Test]
    #[DataProvider('mappingFiles')]
    public function everyClassTheFileNamesExistsInThisFamily(string $file): void
    {
        $xpath = $this->load($file);

        $entities = $xpath->query('//m:entity');
        self::assertNotFalse($entities);
        self::assertCount(1, $entities, $file . ' maps one entity per file');
        $entity = $entities->item(0);
        self::assertInstanceOf(DOMElement::class, $entity);

        $class = $entity->getAttribute('name');
        self::assertTrue(class_exists($class), $file . ' maps ' . $class . ', which does not exist');
        self::assertTrue(str_starts_with($class, self::FAMILY), $file . ' maps ' . $class . ', outside the family');

        $repository = $entity->getAttribute('repository-class');
        if ('' !== $repository) {
            self::assertTrue(class_exists($repository), $file . ' names repository ' . $repository . ', which does not exist');
            self::assertTrue(
                str_starts_with($repository, self::FAMILY . 'Doctrine\\'),
                $file . ' names a repository outside this adapter: ' . $repository,
            );
        }
    }

    #[Test]
    #[DataProvider('mappingFiles')]
    public function aDiscriminatorMapNamesOnlyTheRoot(string $file): void
    {
        $xpath = $this->load($file);
        $roots = $xpath->query('//m:entity');
        self::assertNotFalse($roots);
        $root = $roots->item(0);
        self::assertInstanceOf(DOMElement::class, $root);
        $entries = $xpath->query('//m:discriminator-map/m:discriminator-mapping');
        self::assertNotFalse($entries);

        foreach ($entries as $entry) {
            self::assertInstanceOf(DOMElement::class, $entry);
            self::assertSame(
                $root->getAttribute('name'),
                $entry->getAttribute('class'),
                $file . ' maps a discriminator value to a class other than its own root; a subclass declares itself',
            );
        }
    }

    private function load(string $file): DOMXPath
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = $dom->load($file);
        $errors = libxml_get_errors();
        libxml_use_internal_errors(false);
        self::assertTrue($loaded && [] === $errors, $file . ' is not well-formed XML');

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', self::XML_NS);

        return $xpath;
    }
}
