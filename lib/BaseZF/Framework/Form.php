<?php
/**
 * Form class in /BazeZF/Framework
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Framework_Form extends Zend_Form
{
    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->setDisableTranslator(true);

        $this->addElementPrefixPath('BaseZF_Framework', 'BaseZF/Framework/');
        $this->addPrefixPath('BaseZF_Framework_Form_Element', 'BaseZF/Framework/Form/Element/', 'element');
        $this->addPrefixPath('BaseZF_Framework_Form_Decorator', 'BaseZF/Framework/Form/Decorator/', 'decorator');

        parent::__construct($options);

	}

    /**
     * Return a json array or partial validation restults
     */
    public function processJson($formData)
    {
        $response = array();

        if (!$this->isValidPartial($formData)) {
            $response = $this->getMessages();
        }

        $params = array_keys($formData);
        foreach ($params as $param) {
            if (!isset($response[$param])) {
                $response[$param] = null;
            }
        }

        return $response;
    }

    /**
     * Set default render
     */
	public function render($content = null)
	{
        $this->setAttrib('class', $this->getAttrib('class') . ' formLayout');

		$defaultDecorators = array(
			'FormElements',
			'Form'
		);

		$defaultGroupDecorators = array(
			'FormElements',
			'Fieldset',
		);

		$this->setElementDecorators(array('Composite'));
		$this->setDisplayGroupDecorators($defaultGroupDecorators);
		$this->setSubFormDecorators($defaultDecorators);
		$this->setDecorators($defaultDecorators);

        return parent::render($content);
	}
}

