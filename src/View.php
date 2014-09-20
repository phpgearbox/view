<?php namespace Gears;
////////////////////////////////////////////////////////////////////////////////
// __________ __             ________                   __________              
// \______   \  |__ ______  /  _____/  ____ _____ ______\______   \ _______  ___
//  |     ___/  |  \\____ \/   \  ____/ __ \\__  \\_  __ \    |  _//  _ \  \/  /
//  |    |   |   Y  \  |_> >    \_\  \  ___/ / __ \|  | \/    |   (  <_> >    < 
//  |____|   |___|  /   __/ \______  /\___  >____  /__|  |______  /\____/__/\_ \
//                \/|__|           \/     \/     \/             \/            \/
// -----------------------------------------------------------------------------
//          Designed and Developed by Brad Jones <brad @="bjc.id.au" />         
// -----------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////

use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;

class View
{
	/**
	 * Property: viewsPath
	 * =========================================================================
	 * This is where we store the location of our views.
	 * This can either be a single path. Or an array of paths in effect
	 * creating a views include path.
	 */
	private $viewsPath = null;

	/**
	 * Property: cachePath
	 * =========================================================================
	 * This is where we store the location of our views cache folder.
	 * We default this to a tmp dir, for super quick setup.
	 */
	private $cachePath = '/tmp/gears-views-cache';

	/**
	 * Property: viewFactory
	 * =========================================================================
	 * This is where we store a copy of the actual Laravel View Factory.
	 */
	private $viewFactory = null;

	/**
	 * Property: instance
	 * =========================================================================
	 * This is used as part of the globalise functionality.
	 */
	private static $instance = null;

	/**
	 * Method: __construct
	 * =========================================================================
	 * To setup the Laravel Blade views, create a newt instance.
	 * At minimum we just need a path to the location of your views.
	 *
	 * Example usage:
	 *
	 *     $views = new Gears\View('/path/to/my/view');
	 *     echo $views->make('my-view');
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $views - A path to the views directory. Or an array of paths.
	 *
	 * $options - An array of other options to set. The keys of the array
	 * reflect the names of the properties above.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function __construct($views, $options = array())
	{
		// Save our views path
		if (is_array($views))
		{
			$this->viewsPath = $views;
		}
		else
		{
			$this->viewsPath = [$views];
		}

		// Set the rest of our config
		// Sensible defaults have been set in the properties above
		// So if nothing gets set here, we can continue.
		foreach ($options as $option_key => $option_value)
		{
			if (isset($this->{$option_key}))
			{
				$this->{$option_key} = $option_value;
			}
		}

		// Make sure the cache folder exists
		if (!is_dir($this->cachePath))
		{
			// Lets attempt to create the folder
			if (!mkdir($this->cachePath, 0777, true))
			{
				// Bail out we couldn't create the folder
				throw new \Exception('Blade Cache Folder could not be created!');
			}
		}

		// Make sure the cache folder is writeable
		if (!is_writeable($this->cachePath))
		{
			throw new \Exception('Blade Cache Folder not writeable!');
		}

		// This is used a few times, so lets create it now.
		$files = new Filesystem;

		// Create the view finder
		$finder = new FileViewFinder($files, $this->viewsPath);

		// Create the engine resolver
		$resolver = new EngineResolver;

		// Add the PhpEngine
		$resolver->register('php', function() { return new PhpEngine; });

		// Add the blade engine :)
		$cachePath = $this->cachePath;
		$resolver->register('blade', function() use ($files, $cachePath)
		{
			return new CompilerEngine
			(
				new BladeCompiler($files, $cachePath),
				$files
			);
		});

		// Create the view factory and save it
		$this->viewFactory = new Factory($resolver, $finder, new Dispatcher);
	}

	/**
	 * Method: globalise
	 * =========================================================================
	 * Now in a normal laravel application you can call the
	 * view api like so:
	 * 
	 *     View::make('my-view');
	 * 
	 * This is because laravel has the IoC container with Service Providers and
	 * Facades and other intresting things that work some magic to set this up
	 * for you. Have a look in you main app.php config file and checkout the
	 * aliases section.
	 * 
	 * If you want to be able to do the same in your application you need to
	 * call this method.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $alias - This is the name of the alias to create. Defaults to View
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function globalise($alias = 'View')
	{
		// Create the alias name
		if (substr($alias, 0, 1) != '\\')
		{
			// This ensures the alias is created in the global namespace.
			$alias = '\\'.$alias;
		}

		// Check if a class already exists
		if (class_exists($alias))
		{
			// Bail out, a class already exists with the same name.
			throw new \Exception('Class already exists!');
		}

		// Create the alias
		class_alias('\Gears\View', $alias);

		// Save our instance
		self::$instance = $this;
	}

	/**
	 * Method: __call
	 * =========================================================================
	 * This will pass any unresolved method calls
	 * through to the main view factory object.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $name - The name of the method to call.
	 * $args - The argumnent array that is given to us.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array([$this->viewFactory, $name], $args);
	}

	/**
	 * Method: __callStatic
	 * =========================================================================
	 * This will pass any unresolved static method calls
	 * through to the saved instance.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $name - The name of the method to call.
	 * $args - The argumnent array that is given to us.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * mixed
	 */
	public static function __callStatic($name, $args)
	{
		// Check to see if we have been globalised
		if (empty(self::$instance))
		{
			throw new \Exception('You need to run globalise first!');
		}

		// Run the method from the static instance
		return call_user_func_array([self::$instance, $name], $args);
	}
}