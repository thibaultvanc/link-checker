<?php

namespace Thibaultvanc\LinkChecker;

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

class CheckerResponse
{
    public $pageExists;
    public $statusCode;
    public $linkExists;
    public $rel;
    public $noFollowOk;
    public $anchor;
    public $anchorOk;
    public $isDdestinationOk;
    public $destinationStatusCode;
    public $destinationUrl;
}
