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
        $checker = new LinkChecker();

        
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
        $checker = new LinkChecker();
 
        $response = $checker->url('https://organit.fr/agence_developpement_web_06/competence_web')
                         ->href('NON')
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
        $checker = new LinkChecker();
  
        $response = $checker->url('https://organit.fr')
                          ->href('https://organit.fr/admin')
                          ->verify();
        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $response);
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('nofollow', $response->rel);
        $this->assertFalse($response->noFollowOk);
        $this->assertFalse($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
        $this->assertEquals('https://organit.fr/login', $response->destinationUrl); //////redirection
    }


    /** @test */
    public function the_destination_page_is_broken()
    {
        /* $client = new MockHttpClient(
            [
                new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" >bla bla</a>'),
                new MockResponse('', ['http_code'=>404]),
            ]
        ); */
        $checker = new LinkChecker();

        $response = $checker->url('https://organit.fr')
                        ->href('https://organit.fr/not-exists')
                        ->verify();
        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $response);
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('nofollow', $response->rel);
        $this->assertFalse($response->noFollowOk);
        $this->assertFalse($response->isDdestinationOk);
        $this->assertEquals(404, $response->destinationStatusCode);
    }


    /** @test */
    public function the_anchor_is_ok()
    {
        /*  $client = new MockHttpClient(
             [
                 new MockResponse('<a href="https://organit.fr/agence_developpement_web_06/contact" >good_anchor</a>'),
                 new MockResponse('', ['http_code'=>404]),
             ]
         ); */
        $checker = new LinkChecker();
 
        $response = $checker->url('https://organit.fr')
                         ->href('https://organit.fr/agence_developpement_web_06/contact')
                         ->anchor('Agence Web Nice - Cannes - Grasse ( 06 )')
                         ->verify();
 
        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $response);
 
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('Agence Web Nice - Cannes - Grasse ( 06 )', $response->anchor);
        $this->assertTrue($response->anchorOk);
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }
   
   
   
   
   
    /** @test */
    public function live_test_enrouleur()
    {
        $checker = new LinkChecker();
 
        $response = $checker->url('https://www.cesim.fr/habitat/les-outils-indispensables-pour-entretenir-le-jardin/')
                         ->href(' https://fr.rs-online.com/web/c/connecteurs/connecteurs-secteur-et-iec-et-accessoires/rallonges-electriques-et-enrouleurs/')
                         ->anchor('un enrouleur pour les outils électriques')
                         ->verify();
 
 
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('un enrouleur pour les outils électriques', $response->anchor);
        $this->assertTrue($response->anchorOk);
        $this->assertNull($response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
    }
   


    /** @test */
    public function live_test_redirect()
    {
        $checker = new LinkChecker();
 
        $response = $checker->url('https://www.unaf.fr/spip.php?rubrique226')
                         ->href('http://www.trainbienvivre.fr/presentation-bien-vivre.html')
                         ->anchor('« bien vivre pour bien vieillir »')
                         ->redirect('https://journees-prevention-santepublique.fr/')
                         ->verify();
 
 
        $this->assertTrue($response->pageExists);
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->linkExists);
        $this->assertEquals('&laquo;&nbsp;Bien vivre pour bien vieillir&nbsp;&raquo;', $response->anchor);
        $this->assertTrue($response->anchorOk);
        $this->assertEquals('external', $response->rel);
        $this->assertTrue($response->noFollowOk);
        $this->assertTrue($response->isDdestinationOk);
        $this->assertEquals(200, $response->destinationStatusCode);
        $this->assertEquals('https://journees-prevention-santepublique.fr/', $response->destinationUrl);
    }
}
