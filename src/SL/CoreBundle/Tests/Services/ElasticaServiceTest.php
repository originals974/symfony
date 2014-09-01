<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ElasticaServiceTest extends WebTestCase
{
    private $elasticaService; 

    public function setUp()
    {
       $this->elasticaService = $this->getContainer()->get('sl_core.elastica'); 
    }

    protected function tearDown()
    {
        unset($this->elasticaService); 
    }

    public function testUpdateElasticaConfigFile()
    {
        $elasticaConfigFilePath = '/home/samuel/Sites/symfony/app/config/elastica.yml';

        if(file_exists($elasticaConfigFilePath)){
            unlink($elasticaConfigFilePath);
        } 

        $this->assertFileNotExists($elasticaConfigFilePath);

        $this->elasticaService->updateElasticaConfigFile(1,30); 

        $this->assertFileExists($elasticaConfigFilePath);
    }

    public function testEntitiesToJSTreeData()
    {

    }
}