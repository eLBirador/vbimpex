<?php if (!defined('IDIR')) { die; }
/*======================================================================*\
|| ####################################################################
|| # vBulletin Impex
|| # ----------------------------------------------------------------
|| # All PHP code in this file is Copyright 2000-2014 vBulletin Solutions Inc.
|| # This code is made available under the Modified BSD License -- see license.txt
|| # http://www.vbulletin.com 
|| ####################################################################
\*======================================================================*/
/**
* openBB_003 Import Usergroup module
*
* @package			ImpEx.openBB
*
*/
class openBB_003 extends openBB_000
{
	var $_version 		= '0.0.1';
	var $_dependent 	= '001';
	var $_modulestring 	= 'Import Usergroup';


	function openBB_003()
	{
		// Constructor
	}


	function init(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		if ($this->check_order($sessionobject,$this->_dependent))
		{
			if ($this->_restart)
			{
				if ($this->restart($sessionobject, $displayobject, $Db_target, $Db_source,'clear_imported_usergroups'))
				{
					$displayobject->display_now('<h4>Imported usergroups have been cleared</h4>');
					$this->_restart = true;
				}
				else
				{
					$sessionobject->add_error('fatal',
											 $this->_modulestring,
											 get_class($this) . '::restart failed , clear_imported_usergroups','Check database permissions');
				}
			}


			// Start up the table
			$displayobject->update_basic('title','Import Usergroup');
			$displayobject->update_html($displayobject->do_form_header('index',substr(get_class($this) , -3)));
			$displayobject->update_html($displayobject->make_hidden_code(substr(get_class($this) , -3),'WORKING'));
			$displayobject->update_html($displayobject->make_hidden_code('import_usergroup','working'));
			$displayobject->update_html($displayobject->make_table_header($this->_modulestring));


			// Ask some questions
			$displayobject->update_html($displayobject->make_input_code('Usergroups to import per cycle (must be greater than 1)','usergroupperpage',50));


			// End the table
			$displayobject->update_html($displayobject->do_form_footer('Continue','Reset'));


			// Reset/Setup counters for this
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_done', '0');
			$sessionobject->add_session_var(substr(get_class($this) , -3) . '_objects_failed', '0');
			$sessionobject->add_session_var('usergroupstartat','0');
			$sessionobject->add_session_var('usergroupdone','0');
		}
		else
		{
			// Dependant has not been run
			$displayobject->update_html($displayobject->do_form_header('index',''));
			$displayobject->update_html($displayobject->make_description('<p>This module is dependent on <i><b>' . $sessionobject->get_module_title($this->_dependent) . '</b></i> cannot run until that is complete.'));
			$displayobject->update_html($displayobject->do_form_footer('Continue',''));
			$sessionobject->set_session_var(substr(get_class($this) , -3),'FALSE');
			$sessionobject->set_session_var('module','000');
		}
	}


	function resume(&$sessionobject, &$displayobject, &$Db_target, &$Db_source)
	{
		// Set up working variables.
		$displayobject->update_basic('displaymodules','FALSE');
		$target_database_type	= $sessionobject->get_session_var('targetdatabasetype');
		$target_table_prefix	= $sessionobject->get_session_var('targettableprefix');
		$source_database_type	= $sessionobject->get_session_var('sourcedatabasetype');
		$source_table_prefix	= $sessionobject->get_session_var('sourcetableprefix');


		// Per page vars
		$usergroup_start_at			= $sessionobject->get_session_var('usergroupstartat');
		$usergroup_per_page			= $sessionobject->get_session_var('usergroupperpage');
		$class_num				= substr(get_class($this) , -3);


		// Start the timing
		if(!$sessionobject->get_session_var($class_num . '_start'))
		{
			$sessionobject->timing($class_num ,'start' ,$sessionobject->get_session_var('autosubmit'));
		}


		// Get an array of usergroup details
		$usergroup_array 	= $this->get_openBB_usergroup_details($Db_source, $source_database_type, $source_table_prefix, $usergroup_start_at, $usergroup_per_page);


		// Display count and pass time
		$displayobject->display_now('<h4>Importing ' . count($usergroup_array) . ' usergroups</h4><p><b>From</b> : ' . $usergroup_start_at . ' ::  <b>To</b> : ' . ($usergroup_start_at + count($usergroup_array)) . '</p>');


		$usergroup_object = new ImpExData($Db_target, $sessionobject, 'usergroup');


		foreach ($usergroup_array as $usergroup_id => $usergroup_details)
		{
			$try = (phpversion() < '5' ? $usergroup_object : clone($usergroup_object));
			// Mandatory
			$try->set_value('mandatory', 'importusergroupid',			$usergroup_details['id']);






			/*
			isadmin  	enum('1', '0') 	  	No  	0
			index_canview  	enum('1', '0') 	  	No  	0
			forum_canview  	enum('1', '0') 	  	No  	0
			thread_canview  	enum('1', '0') 	  	No  	0
			thread_canpost  	enum('1', '0') 	  	No  	0
			member_canregister  	enum('1', '0') 	  	No  	0
			member_canviewprofile  	enum('1', '0') 	  	No  	0
			custom  	enum('1', '0') 	  	No  	0
			design_canview  	enum('1', '0') 	  	No  	0
			design_canedit  	enum('1', '0') 	  	No  	0
			settings_canview  	enum('1', '0') 	  	No  	0
			settings_canchange  	enum('1', '0') 	  	No  	0
			member_memberlist  	enum('1', '0') 	  	No  	0
			member_whosonline  	enum('1', '0') 	  	No  	0
			thread_canprint  	enum('1', '0') 	  	No  	0
			index_canviewfaq  	enum('1', '0') 	  	No  	0
			ismoderator  	enum('1', '0') 	  	No  	0
			thead_canmail  	enum('1', '0') 	  	No  	0
			forums_canedit  	enum('1', '0') 	  	No  	0
			image  	varchar(25) 	  	No
			icon  	varchar(25) 	  	No
			myhome_canaccess  	enum('1', '0') 	  	No  	0
			myhome_myprofile  	enum('1', '0') 	  	No  	0
			myhome_mysettings  	enum('1', '0') 	  	No  	0
			myhome_readmsg  	enum('1', '0') 	  	No  	0
			myhome_newmsg  	enum('1', '0') 	  	No  	0
			pm_maxday  	int(4) 	  	No  	50
			myhome_delmsg  	enum('1', '0') 	  	No  	0
			thread_canreply  	enum('1', '0') 	  	No  	0
			avatar_cancustom  	enum('1', '0') 	  	No  	1
			myhome_dlmsg  	enum('1', '0') 	  	No  	0
			myhome_savemsg  	enum('1', '0') 	  	No  	0
			member_canmail  	enum('1', '0') 	  	No  	0
			poll_canvote  	enum('1', '0') 	  	No  	0
			flood  	tinyint(3) 	  	No  	0
			myhome_favorites  	enum('1', '0') 	  	No  	0
			design_cancreateset  	enum('1', '0') 	  	No  	0
			forum_cansee  	enum('0', '1') 	  	No  	1
			search_cansearch
			*/





			// Non Mandatory
			$try->set_value('nonmandatory', 'title',					$usergroup_details['title']);
			/*
			$try->set_value('nonmandatory', 'description',				$usergroup_details['description']);
			$try->set_value('nonmandatory', 'usertitle',				$usergroup_details['usertitle']);
			$try->set_value('nonmandatory', 'passwordexpires',			$usergroup_details['passwordexpires']);
			$try->set_value('nonmandatory', 'passwordhistory',			$usergroup_details['passwordhistory']);
			$try->set_value('nonmandatory', 'pmquota',					$usergroup_details['pmquota']);
			$try->set_value('nonmandatory', 'pmsendmax',				$usergroup_details['pmsendmax']);
			$try->set_value('nonmandatory', 'pmforwardmax',				$usergroup_details['pmforwardmax']);
			$try->set_value('nonmandatory', 'opentag',					$usergroup_details['opentag']);
			$try->set_value('nonmandatory', 'closetag',					$usergroup_details['closetag']);
			$try->set_value('nonmandatory', 'canoverride',				$usergroup_details['canoverride']);
			$try->set_value('nonmandatory', 'ispublicgroup',			$usergroup_details['ispublicgroup']);
			$try->set_value('nonmandatory', 'forumpermissions',			$usergroup_details['forumpermissions']);
			$try->set_value('nonmandatory', 'pmpermissions',			$usergroup_details['pmpermissions']);
			$try->set_value('nonmandatory', 'calendarpermissions',		$usergroup_details['calendarpermissions']);
			$try->set_value('nonmandatory', 'wolpermissions',			$usergroup_details['wolpermissions']);
			$try->set_value('nonmandatory', 'adminpermissions',			$usergroup_details['adminpermissions']);
			$try->set_value('nonmandatory', 'genericpermissions',		$usergroup_details['genericpermissions']);
			$try->set_value('nonmandatory', 'genericoptions',			$usergroup_details['genericoptions']);
			$try->set_value('nonmandatory', 'pmpermissions_bak',		$usergroup_details['pmpermissions_bak']);
			$try->set_value('nonmandatory', 'attachlimit',				$usergroup_details['attachlimit']);
			$try->set_value('nonmandatory', 'avatarmaxwidth',			$usergroup_details['avatarmaxwidth']);
			$try->set_value('nonmandatory', 'avatarmaxheight',			$usergroup_details['avatarmaxheight']);
			$try->set_value('nonmandatory', 'avatarmaxsize',			$usergroup_details['avatarmaxsize']);
			$try->set_value('nonmandatory', 'profilepicmaxwidth',		$usergroup_details['profilepicmaxwidth']);
			$try->set_value('nonmandatory', 'profilepicmaxheight',		$usergroup_details['profilepicmaxheight']);
			$try->set_value('nonmandatory', 'profilepicmaxsize',		$usergroup_details['profilepicmaxsize']);
			*/

			// Check if usergroup object is valid
			if($try->is_valid())
			{
				if($try->import_usergroup($Db_target, $target_database_type, $target_table_prefix))
				{
					$displayobject->display_now('<br /><span class="isucc"><b>' . $try->how_complete() . '%</b></span> :: usergroup -> ' . $usergroup_details['title']);
					$sessionobject->add_session_var($class_num . '_objects_done',intval($sessionobject->get_session_var($class_num . '_objects_done')) + 1 );
				}
				else
				{
					$sessionobject->set_session_var($class_num . '_objects_failed',$sessionobject->get_session_var($class_num. '_objects_failed') + 1 );
					$sessionobject->add_error('warning', $this->_modulestring, get_class($this) . '::import_custom_profile_pic failed.', 'Check database permissions and database table');
					$displayobject->display_now("<br />Found avatar usergroup and <b>DID NOT</b> imported to the  {$target_database_type} database");
				}
			}
			else
			{
				$displayobject->display_now("<br />Invalid usergroup object, skipping." . $try->_failedon);
			}
			unset($try);
		}// End foreach


		// Check for page end
		if (count($usergroup_array) == 0 OR count($usergroup_array) < $usergroup_per_page)
		{
			$sessionobject->timing($class_num,'stop', $sessionobject->get_session_var('autosubmit'));
			$sessionobject->remove_session_var($class_num . '_start');


			$displayobject->update_html($displayobject->module_finished($this->_modulestring,
										$sessionobject->return_stats($class_num, '_time_taken'),
										$sessionobject->return_stats($class_num, '_objects_done'),
										$sessionobject->return_stats($class_num, '_objects_failed')
										));


			$sessionobject->set_session_var($class_num ,'FINISHED');
			$sessionobject->set_session_var('import_usergroup','done');
			$sessionobject->set_session_var('module','000');
			$sessionobject->set_session_var('autosubmit','0');
			$displayobject->update_html($displayobject->print_redirect('index.php','1'));
		}


		$sessionobject->set_session_var('usergroupstartat',$usergroup_start_at+$usergroup_per_page);
		$displayobject->update_html($displayobject->print_redirect('index.php'));
	}// End resume
}//End Class
# Autogenerated on : August 25, 2004, 3:26 pm
# By ImpEx-generator 1.0.
/*======================================================================*/
?>
