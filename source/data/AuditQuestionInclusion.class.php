<?php

	namespace data;
	
	interface iAuditQuestionInclusion
	{
		// Accessors
			function get_inclusion();
			
		// Mutators
			function set_id($value);
			function set_inclusion($value);
			function set_sub_inclusion_item($value);
			
		// Operators
			function xml();
	}
	
	class AuditQuestionInclusion extends Common implements iAuditQuestionInclusion
	{
		private
			$inclusion		= NULL;
		
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
			
			if(is_array($this->inclusion) === TRUE)			
			{
				
				foreach($this->inclusion as $key => $id)
				{								
					$result .= '<row id="'.$id.'" />';									
				}			
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		// Accessors
		public function get_inclusion()
		{
			return $this->inclusion;
		}
		
		// Mutators
		// "sub" mutators prevent multiple instances of the same
		// data member name from different classes on a form
		// from interfering with each other.
		public function set_inclusion($value)
		{
		}
		
		public function set_id($value)
		{
		}
			
		public function set_sub_inclusion_item($value)
		{
			$this->inclusion = $value;		
		}
	}	
?>