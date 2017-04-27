<?php
	
	namespace data;
	
	interface iBiologicalAgentSub
	{
		// Accessors.
		function get_selected();
				
		// Mutators.
		function set_sub_agent_agent($value);
		
		// Parent overloads.
		function set_id($value);
		
		// Operations
		function xml();			
	}
	
	// For sub data when the table is a simple ID/Item list.
	class BiologicalAgentSub extends Common implements iBiologicalAgentSub
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
			public function set_sub_agent_agent($value)
			{
				$this->value = $value;
			}	
	}
?>
