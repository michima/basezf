<?php
/**
 * Form class in /BazeZF/Framework
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract
{
    static $helperWithoutLabel = array(
		'forminfo',
        'formcheckbox',
		'formmulticheckbox',
		'formmultiradio',
	);

    public function buildField()
    {
        $element = $this->getElement();
		$helper  = $element->helper;

		// update attribs : remove helper attribute and merge helper name with class
		$newAttribs = $element->getAttribs();
		$newAttribs['class'] = $helper . ' ' . $element->getAttrib('class');

		// do not display useless label
		if(in_array(strtolower($helper), self::$helperWithoutLabel) ==! false) {
			$newAttribs['label'] = $element->getLabel();
			$newAttribs['label_class'] = 'formLabel' . ucfirst(str_replace('form', '', $helper));
		}

		// clean attribs
		unset($newAttribs['helper']);

        return $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $newAttribs,
            $element->options
        );
    }

    public function buildDescription()
    {
        $desc = $this->getElement()->getDescription();

        if (empty($desc)) {
            return '';
        }

        return '<small>' . $desc . '</small>';
    }

    public function buildLabel()
    {
        $element = $this->getElement();
		$helper  = $element->helper;
        $label = $element->getLabel();

		// do not display useless label
		if(in_array(strtolower($helper), self::$helperWithoutLabel) ==! false) {
			$element->setAttrib('label', $label);
			return '';
		}

        // translate it ?
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }

        return $element->getView()->formLabel($element->getName(), $label);
    }

    public function buildErrors()
    {
        $element  = $this->getElement();

        if (!$element->hasErrors()) {
            return;
        }

        $messages = $element->getMessages();

        // translate it ?
        if ($translator = $element->getTranslator()) {
            foreach ($messages as $key => $value) {
                $messages[$key] = $translator->translate($value);
            }
        }

        return $element->getView()->formErrors($messages);
    }

    public function getContainerClass()
    {
        $element = $this->getElement();

        // render containerClass
        $containerClass = array();

        if ($element->container_class) {
            $containerClass = explode(' ', $element->container_class);
        }

        // add default class
        $containerClass[] = ($element->isRequired() ? 'required' : 'optional');

        if ($element->hasErrors()) {
            $containerClass[] = 'error';
        }

        return (!empty($containerClass) ? implode(' ', $containerClass) : false);
    }

    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }

        if (null === $element->getView()) {
            return $content;
        }

        // render sub parts
        $label     = $this->buildLabel();
        $field     = $this->buildField();
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();


        // render container
        $containerClass = $this->getContainerClass();
        $output = '<div ' . ($containerClass ? 'class="' . $containerClass . '"' : '') . '>'
                . $errors
                . $label
                . $field
                . $desc
                . '</div>';

        // render
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}
