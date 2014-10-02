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

use Gears\Di\Container;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;

class View extends Container
{
	/**
	 * Property: viewsPath
	 * =========================================================================
	 * This is where we store the location of our views.
	 * This can either be a single path. Or an array of paths in effect
	 * creating a views include path. This is injected as the first argument
	 * to the constructor.
	 */
	protected $viewsPath;

	/**
	 * Property: cachePath
	 * =========================================================================
	 * This is where we store the location of our views cache folder.
	 * We default this to a tmp dir, for super quick setup.
	 */
	protected $injectCachePath;

	protected $injectFilesystem;

	protected $injectFileViewFinder;

	protected $injectEngineResolver;

	protected $injectPhpEngine;

	protected $injectBladeEngine;

	protected $injectDispatcher;

	/**
	 * Property: factory
	 * =========================================================================
	 * This is where we store the final Laravel View Factory.
	 */
	protected $injectFactory;

	/**
	 * Property: instance
	 * =========================================================================
	 * This is used as part of the globalise functionality.
	 */
	private static $instance;

	protected function setDefaults()
	{
		$this->cachePath = '/tmp/gears-views-cache';

		$this->filesystem = function()
		{
			return new Filesystem;
		};

		$this->dispatcher = function()
		{
			return new Dispatcher;
		};

		$this->fileViewFinder = function()
		{
			return new FileViewFinder($this->filesystem, $this->viewsPath);
		};

		$this->phpEngine = $this->protect(function()
		{
			return new PhpEngine;
		});

		$that = $this;
		$this->bladeEngine = $this->protect(function() use ($that)
		{
			return new CompilerEngine
			(
				new BladeCompiler($that->filesystem, $that->cachePath),
				$that->filesystem
			);
		});

		$this->engineResolver = function()
		{
			$resolver = new EngineResolver;

			$resolver->register('php', $this->phpEngine);

			$resolver->register('blade', $this->bladeEngine);

			return $resolver;
		};

		$this->factory = function()
		{
			return new Factory
			(
				$this->engineResolver,
				$this->fileViewFinder,
				$this->dispatcher
			);
		};
	}

	public function __construct($viewsPath, $config = [])
	{
		parent::__construct($config);

		if (is_array($viewsPath))
		{
			$this->viewsPath = $viewsPath;
		}
		else
		{
			$this->viewsPath = [$viewsPath];
		}

		// Make sure the cache folder exists
		if (!is_dir($this->cachePath))
		{
			// Lets attempt to create the folder
			if (!mkdir($this->cachePath, 0777, true))
			{
				// Bail out we couldn't create the folder
				throw new Exception('Blade Cache Folder could not be created!');
			}
		}

		// Make sure the cache folder is writeable
		if (!is_writeable($this->cachePath))
		{
			throw new Exception('Blade Cache Folder not writeable!');
		}
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
			throw new Exception('Class already exists!');
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
		return call_user_func_array([$this->factory, $name], $args);
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
			throw new Exception('You need to run globalise first!');
		}

		// Run the method from the static instance
		return call_user_func_array([self::$instance, $name], $args);
	}
}