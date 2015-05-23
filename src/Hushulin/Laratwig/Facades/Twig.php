<?php namespace Hushulin\Laratwig\Facades;
use Illuminate\Support\Facades\Facade;
/**
 * @see \Twig_Environment
 * @see \TwigBridge\Bridge
 */
class Twig extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'twig';
    }
}
