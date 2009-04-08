<?php
/**
 * Bootstrap.php
 *
 * Main Bootstrap
 *
 * @category   BaseZF
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Bootstrap
{
	/**
	 * Current Bootstrap Config
	 */
    protected $_config = array();

	/**
	 * Default config values
	 */
	protected $_defaultConfig = array(

        // debug config
        'debug_enable'          => false,
        'debug_report'          => true,
        'debug_report_from'     => null,
        'debug_report_to'       => null,

		// Controller
		'controller_path'			=> '',
		'controller_helper_path'	=> '',
		'controller_plugins'		=> array(
		),

		'controller_modules'	=> array(
			'default',
			'example',
		),

		// dependency
		'zend_version'			=> '1.7.7',

		// Layout
		'layout_path'			=> '',
		'layout_default' 		=> 'default',
		'layout_content_key'	=> 'content',
		'layout_script_suffix' 	=> '.phtml',
		'layout_inflector' 		=> ':script/layout.:suffix',

		// View
		'view_path'				=> '',
		'view_helper_path'		=> '',
		'view_script_suffix' 	=> '.phtml',
		'view_inflector' 		=> 'scripts/:module/:controller/:action.:suffix',
		'view_helper_paths' 	=> array(
			'BaseZF/Framework/View/Helper' => 'BaseZF_Framework_View_Helper'
		),
	);

	/**
	 * Required config values
	 */
	protected $_requiredConfig = array(
		'controller_path',
		'layout_path',
		'view_helper_path',
		'view_path',
	);

    /**
	 * Initilize Bootstrap
	 */
    public function __construct(array $config = array())
    {
		// load config
		$this->_loadConfig($config);

		try {

			// check required Zend Framework Version
			$zendRequiredVersion = $this->getConfig('zend_version');
			if (!version_compare($zendRequiredVersion, Zend_Version::VERSION, '=')) {
				throw new Exception('Require Zend Framework Version ' . $zendRequiredVersion . ', current is version ' . Zend_Version::VERSION);
			}

			$this->_initLayout();

			$this->_initView();

			$this->_initFrontController();

            $this->_init();

		} catch (Exception $exception) {

			$this->_handleException($exception);
		}
    }

	/**
	 * Initilize Bootstrap
	 */
	abstract protected function _init();

	/**
     * Get available routes
     */
    abstract protected function _getRoutes();

	//
	// Dispath
	//

	/**
	 *
	 */
	public function dispatch(Zend_Controller_Request_Abstract $request = null, Zend_Controller_Response_Abstract $response = null)
    {
		try {

			$this->_preDispatch();

			Zend_Controller_Front::getInstance()->dispatch($request, $response);

			$this->_postDispatch();

		} catch (Exception $exception) {

			$this->_handleException($exception);
		}
	}

	/**
	 *
	 */
	protected function _preDispatch()
	{
	}

	/**
	 *
	 */
	protected function _postDispatch()
	{
	}

	//
	// Error
	//

	/**
	 *
	 */
	protected function _handleException(Exception $exception)
	{
        if (1) {
			$this->_debugException($exception);
		} else {
			$this->_dispatchException($exception);
		}

        //BaseZF_Error_Handler::sendExceptionByMail
	}

    /**
	 *
	 */
	protected function _debugException(Exception $exception)
	{
		throw $exception;
	}

	/**
	 *
	 */
	protected function _dispatchException(Exception $exception)
	{
        // Build request
		$errorHandlerParams = new ArrayObject(
			array(
				'exception' => $exception,
				'type' 		=> Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER
			), ArrayObject::ARRAY_AS_PROPS
		);

		// @todo use Zend_Controller_Request_Simple
		/*
		$request = new Zend_Controller_Request_Simple();
		$request->setModuleName('default')
				->setControllerName('error')
				->setActionName('error')
		*/

		$request = new Zend_Controller_Request_Http();
		$request->setRequestUri('/error-500')
				->setParam('error_foward', true)
				->setParam('error_handler', $errorHandlerParams);

		Zend_Controller_Front::getInstance()->dispatch($request);

		exit();
	}

	//
	// View and Layout initilisation
	//

	/**
	 *
	 */
    protected function _initView()
    {
		// get view config
        $config = $this->getConfig(array(
			'view_path',
			'view_helper_path',
			'view_inflector',
			'view_helper_paths',
		));

        $view = Zend_Layout::getMvcInstance()->getView();

        // set view default path
        $view->addScriptPath($config['view_path']);
        $view->addHelperPath($config['view_helper_path'], 'View_Helper');

		// add view helper paths
		foreach ($config['view_helper_paths'] as $helperPath => $helperClass) {
			$view->addHelperPath($helperPath, $helperClass);
		}

        // set encoding
        $view->setEncoding('UTF-8');

        // configure view render (path and suffix)
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view)
					 ->setViewScriptPathSpec($config['view_inflector']);
    }

	/**
	 *
	 */
    protected function _initLayout()
    {
		// set layout config
        $config = $this->getConfig(array(
			'layout_path',
			'layout_default',
			'layout_content_key',
			'layout_inflector',
		));

        // init layout
		$layout = Zend_Layout::startMvc(array(
			'layoutPath' => $config['layout_path'],
			'layout'     => $config['layout_default'],
			'contentKey' => $config['layout_content_key'],
		));

        // set layout path and suffix
        $layout->setInflectorTarget($config['layout_inflector']);
    }

	//
	// FrontController initilisation
	//

	/**
	 *
	 */
    protected function _initFrontController()
    {
        // init standart router
        $router = new Zend_Controller_Router_Rewrite();

        // init dispatcher with modules controllers
        $dispatcher = new Zend_Controller_Dispatcher_Standard();

        // init front controller
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setRouter($router)
                        ->setDispatcher($dispatcher);

		// init routes
        $this->_initRoute($frontController);

        // init Controller module
        $this->_initControllerModules($frontController);

        // init Controller module
        $this->_initControllerPlugins($frontController);

        // init error plugins
        if (0) {
            $frontController->throwExceptions(true);
        }

		return $this;
    }

	/**
	 * Init routes
	 */
    protected function _initRoute(Zend_Controller_Front $frontController)
    {
        $router = $frontController->getRouter();
        $routes = $this->_getRoutes();

        foreach ($routes as $name => &$route) {
            $router->addRoute($name, $route);
        }

        return $this;
    }

	/**
	 * Init controllers modules
	 */
    protected function _initControllerModules(Zend_Controller_Front $frontController)
    {
        $config = $this->getConfig(array(
			'controller_path',
			'controller_modules',
		));

		$controllerModules = array();
        foreach ($config['controller_modules'] as $controllerModule) {
			$controllerModule = strtolower($controllerModule);
            $controllerModules[$controllerModule] = $config['controller_path'] . '/' . $controllerModule;
        }

        $frontController->setControllerDirectory($controllerModules);

        return $this;
    }

	/**
	 * Init controllers plugins
	 */
    protected function _initControllerPlugins(Zend_Controller_Front $frontController)
    {
		$controllerPlugins = $this->getConfig('controller_plugins');
        foreach ($controllerPlugins as $controllerPlugin => $config) {
             $plugin = new $controllerPlugin($config);
             $frontController->registerPlugin($plugin);
        }

        return $this;
    }

	//
	// Config
	//

	/**
	 *
	 */
	protected function _loadConfig(array $config)
	{
		$this->_config = array_merge($this->_defaultConfig, $config);
	}

	/**
	 *
	 */
	public function setConfig($key, $value)
	{
		if (is_array($key)) {

			$results = array();
			foreach ($key as $id => $value) {
				$this->setConfig($id, $value);
			}

		} else {
			$this->_config[$key] = $value;
		}

		return $this;
	}

	/**
	 *
	 */
	public function getConfig($key)
	{
		if (is_array($key)) {

			$results = array();
			foreach ($key as $value) {
				$results[$value] = $this->getConfig($value);
			}

			return $results;

		} else {

			if(!array_key_exists($key, $this->_config)) {
				throw new Exception('Undefined config key "' . $key . '"');
			}

			return $this->_config[$key];
		}
	}
}

