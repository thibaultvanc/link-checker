<?php

namespace Thibaultvanc\LinkChecker;

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;
use Thibaultvanc\LinkChecker\CheckerResponse;

class LinkChecker
{
    public $url;
    public $tag = 'a';
    public $href;
    public $anchor;
    public $client;
    public $response; //CheckerResponse

    
    public function __construct($httpClient=null) //HttpClientInterface
    {
        $httpClient = $httpClient ?: HttpClient::create(['timeout' => config('link-checker.timeout')]);
        $this->client = new Client($httpClient);
        //dd(__CLASS__. 'line :' .__LINE__, '____   $httpClient   ____', $httpClient);
        $this->response = new CheckerResponse;
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
    public function anchor($anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }
    public function verify() : CheckerResponse
    {
        try {
            $crawler = $this->client->request('GET', $this->url);
             //dd(__CLASS__. 'line :' .__LINE__, '____   $crawler   ____', $this->client->getResponse()->getStatusCode());
            $this->response->pageExists = true;
            $this->response->statusCode = $this->client->getResponse()->getStatusCode();
            if ($this->response->statusCode !== 200) {
                $this->response->pageExists = false;
                return $this->response;
            }
        } catch (\Throwable $th) {
            //dd(__CLASS__. 'line :' .__LINE__, '____   $th->getMessage()   ____', $th->getMessage());
            $this->response->statusCode = 404;
            $this->response->pageExists = false;
            return $this->response;
        }

        


        try {
            $link = $crawler->filter('a[href="' . $this->href . '"]')->link();
        } catch (\Throwable $th) {
            $this->response->linkExists = false;
            return $this->response;
        }
        $this->response->linkExists = !!$link;

        if (! $this->response->linkExists) {
            return $this->response;
        }

        //dd(__CLASS__. 'line :' .__LINE__, '____   $response   ____', $this->response);

        $node = $link->getNode();
        $rel = $node->getAttribute('rel');
        $this->response->rel = $rel ?: null;


        $childNodes = $node->childNodes;
        $this->response->anchor = strip_tags($node->ownerDocument->saveHTML($node));
       
        //dd($this->response->anchor);

        if ($this->anchor) {
           // dd(__CLASS__. 'line :' .__LINE__, '____ H E R E  ____', trim($this->anchor), trim($this->response->anchor));
            $this->response->anchorOk = trim($this->anchor) == trim($this->response->anchor);
            //$this->response->anchorOk = dd(strpos($this->to_camel_case($this->anchor), $this->to_camel_case($this->response->anchor)));
        }
        $this->response->noFollowOk = $rel !== 'nofollow';
        
       
        $destination = $this->client->click($link);
        $destStatus = $this->client->getResponse()->getStatusCode();
        $this->response->isDdestinationOk = $destination->getUri() === $this->href && $destStatus===200;
        $this->response->destinationStatusCode = $destStatus;

        
        return $this->response;
    }


    private function to_camel_case($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        
        // $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', function ($c) {
            return strtoupper($c[1]);
        }, $str);
    }
}
