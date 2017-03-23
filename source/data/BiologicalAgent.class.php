<?php
	
	namespace data;

	interface iBiologicalAgent
	{
		// Accessors.
		function get_information();
		function get_risk_group();
				
		// Mutators.
		function set_information($value);
		function set_risk_group($value);		
	}	
	
	class BiologicalAgent extends Common implements iBiologicalAgent
	{
		protected
			$risk_group 	= NULL,
			$information	= NULL;
			
		public function get_risk_group()
		{
			return $this->risk_group;
		}
		
		public function get_information()
		{
			return $this->information;
		}
	
		// Mutators		
		public function set_risk_group($value)
		{
			$this->risk_group = $value;
		}

		public function set_information($value)
		{
			$this->information = $value;
		}
	}
	
	interface biological_agent_sub_data
	{
		// Accessors.
		function get_selected();
		function get_value();
				
		// Mutators.
		function set_selected($value);
		function set_value($value);
		
		// Parent overloads.
		function set_id($value);
		
		// Operations
		function xml();			
	}
?>
