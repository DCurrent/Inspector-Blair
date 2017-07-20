<?php	
		
	require(__DIR__.'/source/main.php');

	require($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php');

	class class_filter_control extends \data\Common
	{		
		private	$account_filter	= NULL;
		
		public function __construct()
		{
			$this->populate_from_request();	
		}
		
		// Accessors
		
		public function get_account_filter()
		{
			return $this->account_filter;
		}
		
		// Mutators
		public function set_account_filter($value)
		{
			$this->account_filter = $value;
		}
	}
	
	// Initialize filter control object.
	$filter_control = new class_filter_control();	

	// Start page cache.
	$page_obj = new \dc\cache\PageCache();	
		
	$yukon_database->set_sql('{call account_list(@page_current 		= ?,														 
										@page_rows 			= ?,
										@sort_field			= ?,
										@sort_order			= ?,
										@filter_like		= ?)}');	
	
	$params = array(array(-1, 	SQLSRV_PARAM_IN),			// No paging. 
					array(NULL, 		SQLSRV_PARAM_IN),	// No page limit.
					array(2, 	SQLSRV_PARAM_IN),			// Last name.
					array(0, 	SQLSRV_PARAM_IN),			// Ascending.
				   array($filter_control->get_account_filter(), SQLSRV_PARAM_IN));			

	$yukon_database->set_param_array($params);
	$yukon_database->query_run();
	
	$yukon_database->get_line_config()->set_class_name('\data\Account');
	$_obj_data_main_list = $yukon_database->get_line_object_list();	
	
	
	if(is_object($_obj_data_main_list) === TRUE)
	{
		for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
		{						
			$_obj_data_main = $_obj_data_main_list->current();
			?>
			<option value = "<?php echo $_obj_data_main->get_id(); ?>"><?php if($_obj_data_main->get_name_l()) echo $_obj_data_main->get_name_l().', '.$_obj_data_main->get_name_f();?> - <?php echo $_obj_data_main->get_account(); ?></option>
			<?php								
		}
	}

	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>