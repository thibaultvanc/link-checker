<?php

namespace Thibaultvanc\LinkChecker\Tests;

use Goutte\Client;
use Orchestra\Testbench\TestCase;
use Thibaultvanc\LinkChecker\LinkChecker;
use Thibaultvanc\LinkChecker\LinkCheckerFacade;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Thibaultvanc\LinkChecker\LinkCheckerServiceProvider;

class RealLinkCheckerTest extends TestCase
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
    public function real_it_return_if_the_link_exists__happy_path()
    {
        $checker = new LinkChecker(); 

        $response = $checker->url('https://url-not-exists')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertFalse($response->pageExists);
        $this->assertEquals(404, $response->statusCode);
        $this->assertNull($response->linkExists);
        $this->assertNull($response->rel);
        $this->assertNull($response->noFollowOk);
        $this->assertNull($response->isDdestinationOk);
    }


}
