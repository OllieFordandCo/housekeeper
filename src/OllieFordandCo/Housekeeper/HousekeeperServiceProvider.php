<?php namespace OllieFordandCo\Housekeeper;

use Illuminate\Support\ServiceProvider;

class HousekeeperServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('ollie-fordand-co/housekeeper');
		include __DIR__.'/../../routes.php';		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['housekeeper'] = $this->app->share(function($app)
        {
            return new Housekeeper;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('housekeeper');
	}

}
