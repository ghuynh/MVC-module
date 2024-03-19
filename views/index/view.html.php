<?php
require_once( JPATH_COMPONENT.DS.'view.php' );
jimport('mvc_ext.formhelper');
/**
 * EnquireViewIndex for Enquiry module 
 * This class acts as the view helper for the main views of Customer enquiry module. 
 *
 * @author     George Huynh
 * @copyright  2010 
 * @license    Qt License 
 * @version    Release: 1.5.2
 */
class EnquireViewIndex extends EnquireView {

	/**
	 * 
	 * @var FormHelper
	 */
	var $form;
	
	function __construct($config = array()) {
		parent::__construct($config);
		$this->form = new FormHelper();
	}
	
	function display($tpl = null)
	{
		parent::display($tpl);
	}
}
