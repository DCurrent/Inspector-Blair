<?php

	require(__DIR__.'/source/main.php');

	// Temp variable for text work.
	$correction_list_options_temp_finding = NULL;

	class local_class_saa_corrections_params extends \data\Common
	{
		private 
			$category 	= NULL,
			$inclusion	= NULL;
			
		public function __construct()
		{
			$this->populate_from_request();
		}
		
		// Accessors
		public function get_category()
		{
			return $this->category;
		}
		
		public function get_inclusion()
		{
			return $this->inclusion;
		}
		
		// Mutators
		public function set_category($value)
		{
			$this->category = $value;
		}
		
		public function set_inclusion($value)
		{
			$this->inclusion = $value;
		}
	}

	// Set up database.	
	$query = new \dc\yukon\Database($yukon_connection);
	
	// Open local parameters collection object.
	$_obj_params = new local_class_saa_corrections_params();
	
	/*
	// Set SQL String/Stored procedure and parameters.
	$query->set_sql('{call inspection_question_list_select(@category 	= ?,
														@inclusion	= ?)}');
	
	$params = array(array($_obj_params->get_category(), SQLSRV_PARAM_IN),
					array($_obj_params->get_inclusion(), SQLSRV_PARAM_IN));
	
	$query->set_param_array($params);
	$query->query_run();
	
	// Set class object we will push rows from datbase into.
	$query->get_line_config()->set_class_name('class_audit_question_data');
	
	// Establish linked list of objects and populate with rows assuming that 
	// rows were returned. 
	$_obj_data_list_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_list_list = $query->get_line_object_list();
		
	// Default option.
	?>
	
    <option value ="">Select Correction</option>
    
    <?php
	// Iterate through linked list of objects, and output markup for each.
	for($_obj_data_list_list->rewind(); $_obj_data_list_list->valid(); $_obj_data_list_list->next())
	{						
		$_obj_data_list = $_obj_data_list_list->current();
		
		?>        
        <option value = "<?php echo $_obj_data_list->get_id(); ?>"><?php echo $_obj_data_list->get_finding(); ?></option>
        <?php
	}	
	
	
	
	*/
	
	// Categories
	$query->set_sql('{call audit_question_category_list_for_inspection_entry(@page_current 		= ?, 
																			@page_rows = ?, 
																			@inclusion = ?)}');			
	
	$params = array(array(-1,			SQLSRV_PARAM_IN),
						array(NULL,			SQLSRV_PARAM_IN),
						array($_obj_params->get_inclusion(),			SQLSRV_PARAM_IN));

	$query->set_param_array($params);
	$query->query_run();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_field_source_category_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_field_source_category_list = $query->get_line_object_list();
	
	//////////
	// Audit item query. Since we are constructing markup as we go, 
	// there's no getting around multiple executions, so we'll 
	// prepare the query here with bound parameters for
	// maximum speed and efficiency.
	
	// Bound parameters.
	$query_audit_items_params			= array();
	$query_audit_items_param_category 	= NULL;		
	
	// Set up a query object and send SQL string.
	$query_audit_items = new \dc\yukon\Database($yukon_connection);
	$query_audit_items->set_sql('{call inspection_question_list_select(@category 	= ?,
														@inclusion	= ?)}');
	
	// Set up bound parameters.
	$query_audit_items_params = array(array(&$query_audit_items_param_category, SQLSRV_PARAM_IN),
									array(&$inspection_type, SQLSRV_PARAM_IN));
	
	// Prepare query for execution.
	$query_audit_items->set_param_array($query_audit_items_params);
	$query_audit_items->query_prepare();
			
	// Generate a list for new insert. List for existing records are generated per each
	// record loop to 'select' the current record value.
	$correction_list_options = '<option value="'.\dc\yukon\DEFAULTS::NEW_ID.'">Select Item</option>';
	
	// Interate through each category. At every loop we will set our bound 
	// category parameter and execute the item query.
	for($_obj_field_source_category_list->rewind();	$_obj_field_source_category_list->valid(); $_obj_field_source_category_list->next())
	{		
		$_obj_field_source_category = $_obj_field_source_category_list->current();					
		
		// Only add to list if this is a category selected by user, or if all categories are selected. 
		// This is a stop gap solution, and should be replaced with a more effciant query above.
		if($_obj_field_source_category->get_id() == $_obj_params->get_category() || $_obj_params->get_category() == \dc\yukon\DEFAULTS::NEW_ID)
		{
			// Add current category to markup as an option group.
			$correction_list_options .= '<optgroup label="'.$_obj_field_source_category->get_label().'">';
			
			// Set bound parameter and execute prepared query.
			$query_audit_items_param_category = $_obj_field_source_category->get_id();
			$inspection_type = $_obj_params->get_inclusion(); 			
			$query_audit_items->query_execute();		
			
			// Set class object we will push rows from datbase into.
			$query_audit_items->get_line_config()->set_class_name('\data\AuditQuestion');
			
			// Establish linked list of objects and populate with rows assuming that 
			// rows were returned. 
			$_obj_data_list_saa_correction_list = new SplDoublyLinkedList();
			if($query_audit_items->get_row_exists() === TRUE) $_obj_data_list_saa_correction_list = $query_audit_items->get_line_object_list();
			
			// Now loop over all items returned from our prepared query execution.
			for($_obj_data_list_saa_correction_list->rewind();	$_obj_data_list_saa_correction_list->valid(); $_obj_data_list_saa_correction_list->next())
			{	                                                               
				$_obj_data_list_saa_correction = $_obj_data_list_saa_correction_list->current();
				
				// Place finding into a temporary variable for text work.
				$correction_list_options_temp_finding = $_obj_data_list_saa_correction->get_finding();
				
				// Remove all HTML tags and single quotes.
				$correction_list_options_temp_finding = strip_tags($correction_list_options_temp_finding);
				$correction_list_options_temp_finding = htmlspecialchars($correction_list_options_temp_finding, ENT_QUOTES);					
				
				$correction_list_options .= '<option value="'.$_obj_data_list_saa_correction->get_id().'">'.$correction_list_options_temp_finding.'</option>';				
			}
			
			// Close the option group markup for this category.
			$correction_list_options .= '</optgroup>';
		}
	}
	
	echo $correction_list_options;
	
?>

