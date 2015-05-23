<?php namespace Hushulin\Laratwig\Compilers;
use Closure;
use Twig_Environment;
use Twig_Error_Loader;
use Exception;
use InvalidArgumentException;
use Hushulin\Laratwig\Templates\Template;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
class TwigCompiler extends Compiler implements CompilerInterface
{

	protected $twig;

	function __construct(Twig_Environment $twig)
	{
		parent::__construct();
		$this->twig = $twig;
	}


	/**
	 * Get the path to the compiled version of a view.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function getCompiledPath($path)
	{
		return $this->twig->getCacheFilename($path);
	}

	/**
	 * Determine if the given view is expired.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function isExpired($path)
	{
		$time = filemtime($this->getCompiledPath($path));

        return $this->twig->isTemplateFresh($path, $time);
	}

	/**
	 * Compile the view at the given path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function compile($path)
	{
		try {
			$this->load($path);
		} catch (Exception $e) {

		}
	}

	/**
     * Compile the view at the given path.
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return string \TwigBridge\Twig\Template
     */
    public function load($path)
    {
        // Load template
        try {
            $template = $this->twig->loadTemplate($path);
        } catch (Twig_Error_Loader $e) {
            throw new InvalidArgumentException("Error loading $path: ". $e->getMessage(), $e->getCode(), $e);
        }

        if ($template instanceof Template) {
            // Events are already fired by the View Environment
            $template->setFiredEvents(true);
        }

        return $template;
    }


}
