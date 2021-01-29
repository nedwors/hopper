<?php

namespace Nedwors\Hopper;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nedwors\Hopper\Skeleton\SkeletonClass
 */
class HopperFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}
