<?php

namespace Thibaultvanc\LinkChecker\Tests;

use Goutte\Client;
use Orchestra\Testbench\TestCase;
use Thibaultvanc\LinkChecker\LinkChecker;
use Thibaultvanc\LinkChecker\LinkCheckerFacade;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Thibaultvanc\LinkChecker\LinkCheckerServiceProvider;

class LinkCheckerSymfonyTest extends TestCase
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
    public function mock_it_return_if_the_link_exists__happy_path()
    {
  
         $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact">bla bla</a>'),
                new MockResponse('html>Destination Page</html>')
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('bla bla',$response->anchor);
        $this->assertNull($response->anchorOk); //not specified
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }


    /** @test */
    public function The_url_does_not_exists()
    {

        
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="not-match">bla bla</a>', ['http_code'=>404]),
                //new MockResponse('html>Destination Page</html>')
            ]
        );
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

    /** @test */
    public function the_link_is_not_present_on_the_page()
    {
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://www.carlocappai.me/php-testing-symfony-using-guzzles-mock-handler-to-create-a-functional-test">bla bla</a>'),
                new MockResponse('html>Destination Page</html>')
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();


       $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertFalse($response->linkExists);
        $this->assertNull($response->noFollowOk);
        $this->assertNull($response->isDdestinationOk);
        $this->assertNull($response->destinationStatusCode); 
          
    }


    /** @test */
    public function The_link_contains_nofollow()
    {
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" rel="nofollow">bla bla</a>'),
                new MockResponse('html>Destination Page</html>')
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('nofollow',$response->rel);
        $this->assertFalse($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }





    /** @test */
    public function the_destination_page_is_broken()
    {
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" >bla bla</a>'),
                new MockResponse('', ['http_code'=>404]),
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertFalse($response->isDdestinationOk);
        $this->assertEquals(404, $response->destinationStatusCode);
    }
   






    
    /** @test */
    public function the_anchor_is_ok()
    {
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" >good_anchor</a>'),
                new MockResponse('', ['http_code'=>404]),
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->anchor('good_anchor')
                        ->verify();

        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $response);

        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('good_anchor',$response->anchor);
        $this->assertTrue($response->anchorOk);
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertFalse($response->isDdestinationOk);
        $this->assertEquals(404, $response->destinationStatusCode);
    }

    /** @test */
    public function the_anchor_is_contains_html()
    {
        $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" >bla <b>bla</b></a>'),
                new MockResponse('', ['http_code'=>404]),
            ]
        );
        $checker = new LinkChecker($client); 

        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                        ->href('https://organit.fr/agence_developpement_web_06/contact')
                        ->verify();

        //dd(__CLASS__. 'line :' .__LINE__, '____   $response->anchor   ____', $response->anchor);
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertEquals('bla bla',$response->anchor);
        $this->assertFalse($response->isDdestinationOk);
        $this->assertEquals(404, $response->destinationStatusCode);
    }
}
