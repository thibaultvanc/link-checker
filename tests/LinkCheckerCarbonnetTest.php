<?php

namespace Thibaultvanc\LinkChecker\Tests;

use Goutte\Client;
use Orchestra\Testbench\TestCase;
use Thibaultvanc\LinkChecker\LinkChecker;
use Thibaultvanc\LinkChecker\LinkCheckerFacade;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Thibaultvanc\LinkChecker\LinkCheckerServiceProvider;

class LinkCheckerCarbonnetTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LinkCheckerServiceProvider::class];
    }

    protected function getPackageAliases($app)
     {
         return [
             'LinkChecker' => LinkCheckerFacade::class,
         ];
     } 


    /** * @test */
    public function test_carbonnet1()
    {
  
         $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://fr.rs-online.com/web/c/connecteurs/connecteurs-secteur-et-iec-et-accessoires/rallonges-electriques-et-enrouleurs/">un enrouleur pour les outils électriques</a>'),
                new MockResponse('html>Destination Page</html>')
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://www.cesim.fr/habitat/les-outils-indispensables-pour-entretenir-le-jardin/')
                        ->anchor('un enrouleur pour les outils électriques')
                        ->href('https://fr.rs-online.com/web/c/connecteurs/connecteurs-secteur-et-iec-et-accessoires/rallonges-electriques-et-enrouleurs/')
                        ->verify();

                        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $response);
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('un enrouleur pour les outils électriques',$response->anchor);
        $this->assertTrue($response->anchorOk); 
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }


}
