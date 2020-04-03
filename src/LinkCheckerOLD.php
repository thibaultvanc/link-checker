<?php

namespace Thibaultvanc\LinkChecker;

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

//use GuzzleHttp\Client;


class LinkCheckerOLD
{
    public $url;
    public $tag = 'a';
    public $href;
    public $client;

    

    
    public function __construct()
    {
        $this->client = new Client(HttpClient::create(['timeout' => 20]));
        //$this->client = $client ?: new Client();
    }

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }
    public function tag($tag)
    {
        $this->tag = $tag;
        return $this;
    }
    public function href($href)
    {
        $this->href = $href;
        return $this;
    }
    public function verify()
    {
       
        //dd('a[href="' . $this->href . '"]');
        
        $crawler = $this->client->request('GET', $this->url);
        dd(__CLASS__. 'line :' .__LINE__, '____   $crawler   ____', $crawler);
        $link = $crawler->filter('a[href="' . $this->href . '"]')->link();

        //dd(__CLASS__. 'line :' .__LINE__, '____   $link   ____', $link);

        $destination = $this->client->click($link);

        $response = [
            'page_exists' => 'wip',
            'link_exists' => !!$link,
            'is_nofollow' => 'wip',
            'destination_ok' => $destination->getUri() === $this->href
        ];

        return $response;







        dd(__CLASS__. 'line :' .__LINE__, '____   $pagess   ____', $pagess->getUri());
        
        //$link = $crawler->selectLink('Création Graphique')->link();
        //$link = $crawler->selectLink('a[href="' . $this->href . '"]')->link();
        dd(__CLASS__. 'line :' .__LINE__, '____   $crawler   ____', $link);
       
       
       
       
       
        /*  $response = $this->client->get($this->url);

        $content = json_decode($response->getBody()->getContents());
 */




        //
        dd(__CLASS__. 'line :' .__LINE__, '____   $content   ____', $content);
        /* $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        //curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        $html = curl_exec($ch); */

        dd(__CLASS__. 'line :' .__LINE__, '____   $html   ____', $html);
            
        $avaibility = str_contains($html, request('tag'));
    }
}
