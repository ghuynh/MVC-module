<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Enquire Controller for Enquiry module
 * This class acts as the main controller of Customer enquiry module. 
 * The controller check data and sends response to clients who enquire about products and services.
 * This class coding style follows both php coding and Joomla coding standards.
 * @author     George Huynh
 * @copyright  2010 
 * @license    Copyright 2024 by Van (George) H Huynh

Permission is hereby granted to any person obtaining a copy of this software and associated documentation files (the "Software") to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The Qt Commercial License enables businesses to utilize the Qt framework in their proprietary software projects while retaining full control over their source code and intellectual property.

Under the Qt Commercial License, developers and businesses are required to obtain a license and pay a fee for the use of the Qt framework in commercial applications. Please contact me at gwkhuynh@gmail.com to obtain a license in advance before using this software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES, OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT, OR OTHERWISE, ARISING FROM, OUT OF, OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @version    Release: 1.5.2
 */
 class EnquireControllerIndex extends EnquireController {
     /**
	 * 
	 * @var 
	 */
	var $skillset;
	/**
	 * 
	 * @var 
	 */
	var $skillGroup;
    
	/**
     * @name _send
	 * @description Send the email to the clients to confirm that their inquiries have been received. 
	 * @param array $data  the array of user details
     * @return boolean true if the email is sent and false if there is an error
     * @throws none
     * @access public
     */
	function _send($data) {
		global $mainframe;

		jimport( 'joomla.mail.helper' );

		$mailto	= $mainframe->getCfg('mailfrom');
		$from = $mainframe->getCfg('mailfrom');
		$email = $data['email'];
		$subject = JText::_('Online General Enquiry');
		$sender = $data['firstname']." " .$data['lastname'];
		$cc = null;
		$bcc = null;
		$attachment = null;
		
		jimport('smarty.Start_smarty');

		$smarty = Start_smarty::get_smarty(JPATH_THEMES. DS . 'smarty' );
		$smarty->assign('enquire', $data);

		$body = $smarty->fetch('enquire_mail.tpl');
		// Clean the email data
		$subject = JMailHelper::cleanSubject($subject);
		$body	 = JMailHelper::cleanBody($body);
		$sender	 = JMailHelper::cleanAddress($sender);
		return JUtility::sendMail($from, $sender, $mailto, $subject, $body, true, $cc, $bcc, $attachment, $email, $sender);
	}
		
	/**
     * @name _load
	 * @description Check the inquiries table to see if there is any duplicate record exists
	 * @param array $data  the array of user details
     * @param int    $cid  an integer of client id.
     * @param int    $pid  an integer of pakage id.
     * @return boolean: true if the records is duplicate and false if record is unique
     * @throws none
     * @access public
     */
	function _load($data, $cid, $pid) {
		$inquiryModel = JModel::getInstance('Inquiry', 'DwtModel');

		// check the table for duplicate records if identical record is going to be used within 20 seconds
		$timeLimit = 0; 
		$inquiryModel->conditions[] = "`inquiry_user_first_name` = '" . $data['firstname'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_last_name` = '" . $data['lastname'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_telephone` = '" . $data['phone'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_email` = '" . $data['email'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_comments` = '" . $data['comment'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_location` = '" . $data['location'] . "'";
		$inquiryModel->conditions[] = "`inquiry_user_date` >= '" . (time() - $timeLimit) . "'";
				
		if ($inquiryModel->getData())
			return true;
		else
			return false;
	}
	
	/**
     * @name hasPresetItem
	 * @description Check if the course has been preset. 
     * @param int    $cid  an integer of client id.
     * @param int    $pid  an integer of pakage id.
     * @return 
	 *       0 if the client or package exists, 
	 *  	-1 if client is preset, -2 if client and package not exists,
	 *      -3 if both client and packge exist.
     * @throws none
     * @access public
     */
	 
	function hasPresetItems($cid, $pid){
		if (empty($cid) && empty($pid)) {
			return -2;
		} 
                                
        $hasClient = $hasPackage = false;
		
		$clientModel = JModel::getInstance('Client', 'DwtModel');
		if ($clientModel->hasAny('ID='.$cid.' AND active=\'1\'')) {
			$hasClient = true;
		}

		$packageModel = JModel::getInstance('Package', 'DwtModel');

		if ($packageModel->hasAny('ID='.$pid.' AND active=\'1\'')
			&& $clientModel->getClientByPackage($pid) !== false	) {
				$hasPackage = true;
		}

		if ($hasClient && $hasPackage) {
			return -3;
		}
		if ($hasClient || $hasPackage) {
			return 0;
		}
		
		return -1;
	}

	/**
     * @name jssave
	 * @description Save enquiry details. 
	 * @param none
     * @return none
     * @throws none
     * @access public
     */
	function jssave(){
		if (!JRequest::checkToken()) {
			$this->_renderJSON('ACCESS DENIED');
		}

		$result = 'OK';
		// Get parameters 
		$data = JRequest::getVar('enquire', array());

		$cs = empty($data['cs']) ? 0: $data['cs'];
		$pid = empty($data['pid']) ? 0: $data['pid'];

		//Check details' validation
		require_once(JPATH_COMPONENT . DS . 'validators' .DS .'enquire.php');
		$v = new EnquireValidator();
		$error = array();
		
		if (!$v->validates($data)) {
			$error = $v->validationErrors;
		}
		
		if (empty($data['location'])) {
			$error['location'] = 'Please select the city in which you want to do the course';
		}
			
		if ($this->hasPresetClient($cs, $pid) < -2) {
			$error['cs'] = 'Specify either a Client or Package, not both, so that we can identify your enquiry correctly.';
			$error['pid'] = '';
		}
		elseif ($this->hasPresetClient($cs, $pid) < 0) {
			$error['pid'] = 'Please select a course or a package';
		}
		// Check duplicated inquiry
		if ($this->_load($data, $cs, $pid)) {
			$error[] = 'Duplicate request detected. Your enquiry has been already received';
		}
			
		if (empty($error)) {
			$r = $this->_save($data, $cs, $pid);
			if (!$r) {
				$result = "Failed to send this enquiry.";
			}
			else{
				$m = JModuleHelper::getModule('omniture');
				$text = '';
				if (!empty($m)) {
					jimport('mvc_ext.viewhelper');
					require_once(JPATH_ROOT . DS . 'modules' . DS . 'mod_omniture' . DS. 'helper.php');
					$omn_obj = modOmnitureHelper::getTags($m->params);
					$text = ViewHelper::php2js($omn_obj);
				}
				if (!empty($text)) {
					$result .= $text;
				}
			}
		}
		else{
			if (is_array($result)) {
				$result = array_merge($result, $error);
			}
			else
				$result = $error;
		}

		$this->_renderJSON($result);
	}
	/**
     * @name _renderJSON
	 * @description Display details. 
	 * @param array $data - the array of user details
     * @return none
     * @throws none
     * @access public
     */
	function _renderJSON($data) {
		jimport('mvc_ext.viewhelper');
		$mime_type = "application/x-javascript";
		header("Content-type: ".$mime_type);
		echo viewHelper::php2js(array('_RESULT'=>$data));
		die;
	}
        
}
