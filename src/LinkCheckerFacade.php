<?php

namespace Thibaultvanc\LinkChecker;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Thibaultvanc\LinkChecker\Skeleton\SkeletonClass
 */
class LinkCheckerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'link-checker';
    }
}
