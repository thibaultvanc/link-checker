<?php

namespace Thibaultvanc\LinkChecker;

use Goutte\Client;
use PHPHtmlParser\Dom;
use Symfony\Component\HttpClient\HttpClient;
use Thibaultvanc\LinkChecker\CheckerResponse;

class LinkChecker
{
    public $url;
    public $tag = 'a';
    public $href;
    public $anchor;
    public $client;
    public $html;
    public $response; //CheckerResponse

    
    public function __construct() //HttpClientInterface
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        //curl_setopt($ch, CURLOPT_POST, true);
        
        $this->client = $ch;
        $this->response = new CheckerResponse;
    }

    public function url($url)
    {
        $this->url = $url;
        curl_setopt($this->client, CURLOPT_URL, $this->url);
        return $this;
    }
    public function tag($tag)
    {
        $this->tag = $tag;
        return $this;
    }
    public function href($href)
    {
        $this->href = trim($href);
        return $this;
    }
    public function anchor($anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }


    public function verify() : CheckerResponse
    {
        $this->html = curl_exec($this->client);
        $info = curl_getinfo($this->client);

        /**
         * Page exists
         */
        if (curl_errno($this->client)) { //error
            $this->response->statusCode = 404;
            $this->response->pageExists = false;
            return $this->response;
        //echo 'La requête a mis ' . $info['total_time'] . ' secondes à être envoyée à ' . $info['url'];
        } else {
            $this->response->statusCode = $info['http_code'];
            $this->response->pageExists = $this->response->statusCode === 200;
        }

        /**
         * link exists
         */
        

        $dom = new Dom;
        $dom->load($this->html);
        $a = $dom->find('a[href="' . $this->href . '"]');




  

        // dd(__CLASS__. 'line :' .__LINE__, '____   $a   ____', $a[0]->getAttribute['rel']);
        if (isset($a[0])) {
            //dd(__CLASS__. 'line :' .__LINE__, '____ H E R E  ____', $this->response);

            $this->response->linkExists = true;
            $this->response->rel = $a[0]->getAttribute('rel');
            /* try {
                dd('sdq', $a[0]->getAttribute('rel'));
            } catch (\Throwable $th) {
                $this->rel = null;
            } */
            $this->response->noFollowOk = $this->response->rel !== 'nofollow';



            $this->response->anchor = $a[0]->innerHtml;
            if ($this->anchor) {
                $this->response->anchorOk = $this->response->anchor == $this->anchor;
            }


            /**
             * destination page
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
            curl_setopt($ch, CURLOPT_URL, $this->href);
            curl_exec($ch);
            $info = curl_getinfo($ch);

            //curl_setopt($ch, CURLOPT_POST, true);
            if (curl_errno($ch)) { //error
                //dd(__CLASS__. 'line :' .__LINE__, '____ H E R E  ____', $info);
                $this->response->destinationStatusCode = $info['http_code'];
                $this->response->isDdestinationOk = false;
                $this->response->destinationUrl = $info['url'];
                return $this->response;
            //echo 'La requête a mis ' . $info['total_time'] . ' secondes à être envoyée à ' . $info['url'];
            } else {
                $this->response->destinationStatusCode = $info['http_code'] ;
                $this->response->isDdestinationOk = $info['url'] === $this->href && $info['http_code'] === 200;
                $this->response->destinationUrl = $info['url'];
                //dd(__CLASS__. 'line :' .__LINE__, '____ H E R E  ____', $info, $this->response);
            }
        } else {
            $this->response->linkExists = false;
            return $this->response;
        }

        return $this->response;
    }
}
