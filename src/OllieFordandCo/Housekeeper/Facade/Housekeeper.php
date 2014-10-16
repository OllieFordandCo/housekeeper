<?php namespace OllieFordandCo\Housekeeper\Facade;

use Illuminate\Support\Facades\Facade;

class Housekeeper extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'housekeeper'; }

}