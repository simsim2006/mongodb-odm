<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Tests\BaseTest;

class GH561Test extends BaseTest
{
    public function testPersistMainDocument(): void
    {
        $embeddedDocument = new GH561EmbeddedDocument();
        $embeddedDocument->embeddedDocuments->add(new GH561AnotherEmbeddedDocument('foo'));

        $document = new GH561Document();
        $document->embeddedDocuments->add($embeddedDocument);

        $this->dm->persist($document);
        $this->dm->flush();
        $this->dm->clear();

        $document = $this->dm->find(GH561Document::class, $document->id);
        $this->assertInstanceOf(GH561Document::class, $document);
        $this->assertCount(1, $document->embeddedDocuments);

        $embeddedDocument = $document->embeddedDocuments->first();
        $this->assertInstanceOf(GH561EmbeddedDocument::class, $embeddedDocument);
        $this->assertCount(1, $embeddedDocument->embeddedDocuments);

        $anotherEmbeddedDocument = $embeddedDocument->embeddedDocuments->first();
        $this->assertInstanceOf(GH561AnotherEmbeddedDocument::class, $anotherEmbeddedDocument);
        $this->assertEquals('foo', $anotherEmbeddedDocument->name);
    }
}

/** @ODM\Document */
class GH561Document
{
    /**
     * @ODM\Id
     *
     * @var string|null
     */
    public $id;

    /**
     * @ODM\EmbedMany(targetDocument=GH561EmbeddedDocument::class, strategy="set")
     *
     * @var Collection<int, GH561EmbeddedDocument>
     */
    public $embeddedDocuments;

    public function __construct()
    {
        $this->embeddedDocuments = new ArrayCollection();
    }
}

/** @ODM\EmbeddedDocument */
class GH561EmbeddedDocument
{
    /**
     * @ODM\EmbedMany(targetDocument=GH561AnotherEmbeddedDocument::class, strategy="set")
     *
     * @var Collection<int, GH561AnotherEmbeddedDocument>
     */
    public $embeddedDocuments;

    public function __construct()
    {
        $this->embeddedDocuments = new ArrayCollection();
    }
}

/** @ODM\EmbeddedDocument */
class GH561AnotherEmbeddedDocument
{
    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
