<?php	
		
	require($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php');

	/*
	facility
	Damon Vaughn Caskey
	2014-07-16
	
	Output facility options. Used to generate facility drop list contents.
	*/
	
	abstract class FACILITY_COL_ORDER
	{
		const CODE_FIRST	= 0;
		const ADDRESS_FIRST	= 1;
	}
	
	class post
	{
		private
			$col_order,
			$code,
			$name,
			$address,
			$filter,
			$current;
		
		public function __construct() 
		{		
			// Interate through each class variable.
       		foreach($this as $key => $value) 
			{			
				// If we can find a matching a post var with key matching
				// key of current object var, set object var to the post value. 
				if(isset($_POST[$key]))
				{					
					$this->$key = $_POST[$key];           						
				}
			}	
	 	}
		
		public
			function get_current()
			{
				return $this->current;
			}
		
			function get_address()
			{
				return $this->address;
			}
			
			function get_code()
			{
				return $this->code;
			}
			
			function get_col_order()
			{
				return $this->col_order;
			}			
			
			function get_name()
			{
				return $this->name;
			}	
			
			function get_filter()
			{				
				return $this->filter;
			}
	}
	
	$post = new post();
	
	// Database objects.
	$db			= NULL;	// Database.
	$query		= NULL;	// Query.
		
	// UK Space database objects.	
	$line_all	= NULL;	// Line object array.
	$line		= NULL;	// Individual line.
	
	$markup		= NULL; // Result markup.	
	
	// Initialize DB connection and query objects.			
	$db = new Connect();		
	$query = new \dc\yukon\Database($db);	
	
	// Set SQL and parameter string.	
	$query->set_sql("SELECT code, name, address FROM vw_uk_space_facility WHERE (name LIKE ?) ORDER BY name");
	
	$params = array('%'.$post->get_filter().'%');
	
	$query->set_params($params);
	$query->query_run();		
	
	// There should always be records, but verify just in case.
	if($query->get_row_exists())
	{
		// Set class name and create an array of objets from query results.
		$query->get_line_config()->set_class_name('post');
		$line_all = $query->get_line_object_all();
		
		// Interate through array of result objects.
		foreach ($line_all as $line)
		{	
			
			$selected = NULL;
			
			if($post->get_current() && $post->get_current() == $line->get_code())
			{
				$selected = ' selected ';
			}
					
			// Opening markup for option.
			$markup .= '<option value="'.$line->get_code().'"'.$selected.'>';
			
			// Add item to markup based on user selected column order.
			switch($post->get_col_order())
			{
				default:
				case FACILITY_COL_ORDER::CODE_FIRST:
					$markup .= $line->get_code().' - '.ucwords(strtolower($line->get_name()));
					break;
				case FACILITY_COL_ORDER::ADDRESS_FIRST:
					$markup.= ucwords(strtolower($line->get_address())).'  - '.ucwords(strtolower($line->get_name()));
					break;			
			}
			
			// Close markup for option.		
			$markup.= '</option>'.PHP_EOL;
		}
	}	
	
	// Output completed markup.
	echo $markup;	
?>