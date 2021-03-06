<?php

	abstract class APPLICATION_SETTINGS
	{
		const
			VERSION 		= '0.1.1',
			NAME			= 'Inspector Blair',
			DIRECTORY_PRIME	= '/apps/blair',
			TIME_FORMAT		= 'Y-m-d H:i:s',
			PAGE_ROW_MAX	= 25;
	}

	abstract class DATABASE
	{
		const 
			HOST 		= 'gensqlagl.ad.uky.edu\general',	// Database host (server name or address)
			NAME 		= 'inspection',					// Database logical name.
			USER 		= 'EHSInfo_User',				// User name to access database.
			PASSWORD	= 'ehsinfo',					// Password to access database.
			CHARSET		= 'UTF-8';						// Character set.
	}

	abstract class MAILING
	{
		const
			TO		= '',
			CC		= '',
			BCC		= 'dc@caskeys.com',
			SUBJECT = 'Inspector Blair Alert',
			FROM 	= 'ehs_noreply@uky.edu';
	}
	
	abstract class SESSION_ID
	{
		const
			LAST_BUILDING	= 'id_last_building';	// Last building choosen by user.
	}

?>