<?php

namespace AnyContent\Client;

use CMDL\Parser;

use AnyContent\Client\Client;
use AnyContent\Client\Record;
use AnyContent\Client\Repository;

class RepositoryInfoTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var $client Client
     */
    public $client = null;


    public function setUp()
    {

        // Connect to repository
        $client = new Client('http://anycontent.dev/1/example');
        $client->setUserInfo('john@doe.com', 'John', 'Doe');
        $this->client = $client;
    }


    public function testHasContentTypes()
    {
        /** @var Repository $repository */
        $repository = $this->client->getRepository();

        $this->assertTrue($repository->hasContentType('example01'));
        $this->assertFalse($repository->hasContentType('example99'));
    }


    public function testGetContentTypes()
    {
        /** @var Repository $repository */
        $repository   = $this->client->getRepository();
        $contentTypes = $repository->getContentTypes();
        foreach ($contentTypes as $contentTypeName => $contentTypeTitle)
        {
            $this->assertTrue($repository->hasContentType($contentTypeName));
            $this->assertInstanceOf('CMDL\ContentTypeDefinition', $repository->getContentTypeDefinition($contentTypeName));
            $this->assertEquals($repository->getContentTypeDefinition($contentTypeName)->getName(), $contentTypeName);
        }

    }


    public function testContentCounts()
    {
        $info = $this->client->getRepositoryInfo();
        $this->assertEquals(5, $info['content']['example01']['count']);

        $info = $this->client->getRepositoryInfo('live');
        $this->assertEquals(0, $info['content']['example01']['count']);

        $info = $this->client->getRepositoryInfo('default', 'none', 600);
        $this->assertEquals(0, $info['content']['example01']['count']);

        $cmdl = $this->client->getCMDL('example01');

        $contentTypeDefinition = Parser::parseCMDLString($cmdl);
        $contentTypeDefinition->setName('example01');

        $record = new Record($contentTypeDefinition, 'New Record 6');
        $record->setProperty('article', 'Test 6');
        $id = $this->client->saveRecord($record);
        $this->assertEquals(6, $id);

        $info = $this->client->getRepositoryInfo();
        $this->assertEquals(6, $info['content']['example01']['count']);

        /** @var Repository $repository */
        $repository   = $this->client->getRepository();
        $repository->selectContentType('example01');
        $this->assertEquals(6, $repository->getRecordsCount());
    }
}