<?php namespace Hushulin\Laratwig;

use Illuminate\View\ServiceProvider;
use Hushulin\Laratwig\Compilers\TwigCompiler;
use Hushulin\Laratwig\Engines\CompilerEngine;

class LaratwigServiceProvider extends ServiceProvider {

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
		$this->package('hushulin/laratwig');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
		$this->registerEngineResolver();

		$this->registerViewFinder();

		// Once the other components have been registered we're ready to include the
		// view environment and session binder. The session binder will bind onto
		// the "before" application event and add errors into shared view data.
		$this->registerFactory();

		$this->registerSessionBinder();
	}

	/**
	 * Register the engine resolver instance.
	 *
	 * @return void
	 */
	public function registerEngineResolver()
	{
		$this->app->bindShared('view.engine.resolver', function()
		{
			$resolver = new EngineResolver;

			// Next we will register the various engines with the resolver so that the
			// environment can resolve the engines it needs for various views based
			// on the extension of view files. We call a method for each engines.
			foreach (array('php', 'blade' , 'twig') as $engine)
			{
				$this->{'register'.ucfirst($engine).'Engine'}($resolver);
			}

			return $resolver;
		});
	}

	public function registerTwigEngine($resolver)
	{
		$app = $this->app;

		// The Compiler engine requires an instance of the CompilerInterface, which in
		// this case will be the Blade compiler, so we'll first create the compiler
		// instance to pass into the engine so it can compile the views properly.
		$app->bindShared('twig.compiler', function($app)
		{
			$cache = $app['path.storage'].'/views';

			return new TwigCompiler($app['files'], $cache);
		});

		$resolver->register('twig', function() use ($app)
		{
			return new CompilerEngine($app['twig.compiler'], $app['files']);
		});
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
