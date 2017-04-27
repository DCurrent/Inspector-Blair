<?php

	namespace data;
	
	interface iAuditQuestionReference
	{
		// Accessors
			function get_reference();
			
		// Mutators
			function set_id($value);
			function set_reference($value);
			function set_sub_reference_item($value);
			
		// Operators
			function xml();
	}
	
	class AuditQuestionReference extends Common implements iAuditQuestionReference
	{
		private
			$reference		= NULL;
		
		// Get and return an xml string for database use.
		
		public function xml()
		{
			$result = '<root>';
			
			if(is_array($this->id) === TRUE)			
			{
				foreach($this->id as $key => $id)
				{								
					$result .= '<row id="'.$id.'">';
					$result .= '<details>'.htmlspecialchars($this->reference[$key]).'</details>';
					$result .= '</row>';									
				}			
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		// Accessors
		public function get_reference()
		{
			return $this->reference;
		}
		
		// Mutators
		// "sub" mutators prevent multiple instances of the same
		// data member name from different classes on a form
		// from interfering with each other.
		public function set_reference($value)
		{
		}
		
		public function set_id($value)
		{
		}
		
		public function set_sub_reference_id($value)
		{
			$this->id = $value;		
		}
		
		public function set_sub_reference_item($value)
		{
			$this->reference = $value;		
		}
	}	
?>