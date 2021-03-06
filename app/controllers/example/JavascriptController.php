<?php
/**
 * JavascriptController.php
 *
 * @category   MyProject_Controller
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class Example_JavascriptController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout
     */
    protected $_defaultLayout = 'example';

    public function indexAction()
    {
    }

    public function ajaxlinkAction()
    {
        if ($this->isAjax) {

           // callback for ajax link with HTML
           if ($this->_getParam('html') == 1) {

                // execute javascript on callback an return ajax view content
                $this->_makeAjaxHtml(); //-> the view file is now ajaxlink.ajax.phtml

                // add javascript
                $this->_addAjax('alert("you click on me");');
                $this->_addAjax('alert("you can use responseTree: " + responseTree);');
                $this->_addAjax('alert("you can use responseElements: " + responseElements);');
                $this->_addAjax('alert("you can use responseHTML: " + responseHTML);');
                $this->_addAjax('alert("you can use originElement: " + originElement);');

                $this->_addAjax('originElement.appendText(" (clicked)");');
                $this->_addAjax('$("ajaxLinkCallbackValue").set("html", responseHTML);');

           // callback for ajax link without HTML
           } else {

                // execute javascript on callback
                $this->_makeAjax(); //-> view render is now disable
                $this->_addAjax('alert("you click on me");');
                $this->_addAjax('alert("you can allso update me with originElement var");');

                $this->_addAjax('originElement.appendText(" (clicked)");');
           }
        }
    }

    public function ajaxformvalidateAction()
    {
    }

    public function autocompleterAction()
    {
    }

    public function lightboxAction()
    {
    }

    public function tooltipsAction()
    {
    }

    public function notifyAction()
    {
    }

    public function flashAction()
    {
    }

    public function sortablesAction()
    {
    }

    public function slidelistAction()
    {
    }

    public function starratingsAction()
    {
    }
}

