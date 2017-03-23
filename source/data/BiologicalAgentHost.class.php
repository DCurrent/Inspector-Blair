<?php
	
	namespace data;
	
	interface iBiologicalAgentHost
	{		
		// Accessors
		function get_selected();
		
		// Mutators 
		function set_sub_host($value);
		
		// Parent overloads.
		function set_id($value);
		
		// Operations.
		// Get and return an xml string for database use.
		function xml();			
	}
	
	// For sub data when the table is a simple ID/Item list.
	class BiologicalAgentHost extends Common implements iBiologicalAgentHost
	{	
		protected
			$value 		= NULL,
			$selected	= NULL;
	
		// Get and return an xml string for database use.
		public function xml()
		{			
			$result = NULL;
			
			// If there are no rows at all in the html form, we send a
			// blank xml.
			if(is_array($this->value) == TRUE)
			{			
				$result = '<root>';
							
				foreach($this->value as $key => $value)
				{					
					$result .= '<row id="'.$value.'">';	
					$result .= '</row>';							
				}
				
				$result .= '</root>';
			}
			
			return $result;
		}	
		
		// Accessors
			public function get_selected()
			{
				return $this->selected;
			}
		
		
		// Overide conflicting functions from
		// parent class.
			public function set_id($value)
			{
			}
		
		// Mutators 
			public function set_sub_host($value)
			{
			}
			
			public function set_sub_host_host($value)
			{
				$this->value = $value;
				
				var_dump($this->value);
			}	
	}
?>
