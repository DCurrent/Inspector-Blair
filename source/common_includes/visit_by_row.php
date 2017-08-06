<?php
	require(__DIR__.'/../../source/main.php');

	// Output options markup for visit by select.
	function options_markup(SplDoublyLinkedList $_list, $select_target = NULL)
	{
		$result		= NULL;
		$current 	= NULL;

		if(is_object($_list) === TRUE)
		{        
			// Generate table row for each item in list.
			for($_list->rewind(); $_list->valid(); $_list->next())
			{	                                                               
				$current = $_list->current();

				$value 		= $current->get_id();																
				$label		= $current->get_name_l().', '.$current->get_name_f();
				$selected 	= NULL;

				if($value == $select_target)
				{
					$selected = ' selected ';
				}									

				$result .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';                 
			}
		}
		
		return $result;
	}

	class ConfigLocal extends data\Common
	{
		private $id_guid;
		private $select_target;
		
		// Accessors
		public function get_id_guid()
		{
			return $this->id_guid;
		}
		
		public function get_select_target()
		{
			return $this->select_target;
		}
		
		// Mutators
		public function set_id_guid($value)
		{
			$this->id_guid = $value;
		}
		
		public function set_select_target($value)
		{
			$this->select_target = $value;
		}
	}

	$config = new ConfigLocal();
	$config->populate_from_request();


	$guid 			= $config->get_id_guid();
	$option_list	= NULL;   	

	// --Accounts (Inspector)
	$_obj_field_source_account_list = new \data\Account();

	$yukon_database->set_sql('{call account_list_inspector()}');
	$yukon_database->query_run();

	$yukon_database->get_line_config()->set_class_name('\data\Account');

	$_obj_field_source_account_list = new SplDoublyLinkedList();
	if($yukon_database->get_row_exists() === TRUE) $_obj_field_source_account_list = $yukon_database->get_line_object_list();

	//
	$option_list = options_markup($_obj_field_source_account_list, $config->get_select_target());
?>

<div class="form-group filter_row" id="group_visit_by_row_<?php echo $guid; ?>">
	<div class="col-md-11 col-xs-9 col-9" id="filter_visit_by_select_container_<?php echo $guid; ?>">
		<select 
			name 	= "visit_by[]"
			id		= "visit_by_<?php echo $guid; ?>" 							
			class	= "form-control disabled">
			<option value="">Select Visitor</option>
			<?php echo $option_list; ?>
		</select>											
	</div>	

	<div class="col-xs-1 col-1" id="filter_visit_by_remove_container_<?php echo $guid; ?>">		
		<button 
		type	= "button"
		class 	= "btn btn-danger btn-sm filter_row_remove"
		name	= "filter_row_remove"
		id		= "filter_row_remove_<?php echo $guid; ?>"><span class="glyphicon glyphicon-minus"></span></button>				
	</div>
</div>

<script>
	// Initialize a remove listener for this row.
	$(".filter_row_remove").on("click", filter_row_remove);
</script>