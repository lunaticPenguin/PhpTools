<?php
namespace App\Observers;

/**
 * Class AbstractObserver.
 * The parent class of all others observers
 *
 * @package App\Observers
 */
abstract class AbstractObserver
{
    /**
     * Register observer's methods to specified hooks
     */
    public abstract function load();

    /**
     * Returns observer's name
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }
}
