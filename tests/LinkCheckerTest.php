<?php

namespace Thibaultvanc\LinkChecker\Tests;

use Goutte\Client;
use Orchestra\Testbench\TestCase;
use Thibaultvanc\LinkChecker\LinkChecker;
use Thibaultvanc\LinkChecker\LinkCheckerFacade;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Thibaultvanc\LinkChecker\LinkCheckerServiceProvider;

class LinkCheckerTest extends TestCase
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

    /** @test */
    public function it_connect_to_the_url()
    {
        $checker = new LinkChecker();

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('Agence Web Nice - Cannes - Grasse ( 06 )', $response->anchor);
        $this->assertNull($response->anchorOk); //not specified
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }


    /** @test */
    public function The_url_does_not_exists()
    {
        $client =
            [
                '<a href="not-match">bla bla</a>',
                ['http_code'=>404],
                //new MockResponse('html>Destination Page</html>')
            ]
        ;


        $checker = new LinkChecker($client);

        
        $response = $checker->url('https://organit.com/not-exists') //not Exists
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertFalse($response->pageExists);
        $this->assertEquals(404, $response->statusCode);
        $this->assertNull($response->linkExists);
        $this->assertNull($response->noFollowOk);
        $this->assertNull($response->isDdestinationOk);
        $this->assertNull($response->destinationStatusCode);
    }
}
