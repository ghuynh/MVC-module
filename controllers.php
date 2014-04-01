<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Enquire Controller for Enquiry module 
 * This class acts as the main controller of Customer enquiry module. 
 * The controller check data and sends response to clients who enquire about products and services
 *
 * @author     George Huynh
 * @copyright  2010 
 * @license    PHP License 3.01
 * @version    Release: 1.5.2
 */
 class EnquireControllerIndex extends EnquireController {
     /**
	 * 
	 * @var DwtModelSkilloutcome
	 */
	var $skillOutcome;
	/**
	 * 
	 * @var DwtModelSoftwaregrouip
	 */
	var $softwareGroup;
    
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
	 * @description Check the inquiries table to see if there is duplicate record exist
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

	
}
