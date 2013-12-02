<?php namespace Gewora\Envato;

/**
 * Starts the Envato Wrapper
 * 
 *
 * @package Gewora/Envato
 * @author Gewora <admin@gewora.net>
 * @copyright Copyright (c) 2013 by Gewora Project Team
 * @license BSD-3-Clause
 * @version 1.0.0
 * @access public
 */

use Illuminate\Support\ServiceProvider;

class EnvatoServiceProvider extends ServiceProvider {

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
		$this->package('gewora/envato');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
            $this->app['envato'] = $this->app->share(function($app)
            {
              return new Envato($app['config']);
            });
	
            $this->app->booting(function()
            {
              $loader = \Illuminate\Foundation\AliasLoader::getInstance();
              $loader->alias('Envato', 'Gewora\Envato\Facades\Envato');
            });           
        }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('envato');
	}

}
