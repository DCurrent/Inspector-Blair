<?php	
		
	require(__DIR__.'/source/main.php');

	require($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php');

	class class_filter_control
	{		
		private	$data_common = NULL;
		private	$name_like	= NULL;
		
		public function __construct()
		{
			$this->data_common = new \data\Common();	
		}
		
		// Accessors
		public function get_data_common()
		{
			return $this->data_common;
		}
		
		public function get_name_like()
		{
			return $this->name_like;
		}
		
		// Mutators
		public function set_name_like($value)
		{
			$this->name_like = $value;
		}
		
		public function populate_from_request()
		{
			$this->data_common->populate_from_request();
		}
	}
	
	
	$filter_control = new class_filter_control();	
	$filter_control->set_name_like('bar');
	echo $filter_control->get_name_like();

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
				   array($filter_control->get_name_like(), SQLSRV_PARAM_IN));			

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