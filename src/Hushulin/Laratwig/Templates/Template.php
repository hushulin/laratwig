<?php namespace Hushulin\Laratwig\Templates;
use Twig_Template;
use Illuminate\View\View;

abstract class Template extends Twig_Template
{

	/**
     * @var bool Have the creator/composer events fired.
     */
    protected $firedEvents = false;

    /**
     * {@inheritdoc}
     */
    public function display(array $context, array $blocks = [])
    {
        if (!isset($context['__env'])) {
            $context = $this->env->mergeShared($context);
        }

        if ($this->shouldFireEvents()) {
            $context = $this->fireEvents($context);
        }

        parent::display($context, $blocks);
    }

    /**
     * Fire the creator/composer events and return the modified context.
     *
     * @param $context Old context.
     *
     * @return array New context if __env is passed in, else the passed in context is returned.
     */
    public function fireEvents($context)
    {
        if (!isset($context['__env'])) {
            return $context;
        }

        /** @var \Illuminate\View\Factory $env */
        $env  = $context['__env'];
        $view = new View(
            $env,
            $env->getEngineResolver()->resolve('twig'),
            $this->getNormalizedName($env),
            null,
            $context
        );
        $env->callCreator($view);
        $env->callComposer($view);
        return $view->getData();
    }

    /**
     * Get the normalized name, for creator/composer events
     *
     * @param  \Illuminate\View\Factory $viewEnvironment
     * @return string
     */
    protected function getNormalizedName($viewEnvironment)
    {
        $paths = $viewEnvironment->getFinder()->getPaths();
        $name = $this->getTemplateName();

        // Replace absolute paths, trim slashes, remove extension
        $name = str_replace($paths, '', $name);
        $name = ltrim($name, '/');

        if (substr($name, -5, 5) === '.twig') {
            $name = substr($name, 0, -5);
        }

        return $name;
    }

    /**
     * Determine whether events should fire for this view.
     *
     * @return bool
     */
    public function shouldFireEvents()
    {
        return !$this->firedEvents;
    }

    /**
     * Set the firedEvents flag, to make sure composers/creators only fire once.
     *
     * @param bool $fired
     *
     * @return void
     */
    public function setFiredEvents($fired = true)
    {
        $this->firedEvents = $fired;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttribute(
        $object,
        $item,
        array $arguments = [],
        $type = Twig_Template::ANY_CALL,
        $isDefinedTest = false,
        $ignoreStrictCheck = false
    ) {
        // We need to handle accessing attributes on an Eloquent instance differently
        if (Twig_Template::METHOD_CALL !== $type and is_a($object, 'Illuminate\Database\Eloquent\Model')) {
            // We can't easily find out if an attribute actually exists, so return true
            if ($isDefinedTest) {
                return true;
            }

            // Call the attribute, the Model object does the rest of the magic
            return $object->$item;
        } else {
            return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        }


    }
}
