<?php

	namespace data;
	
	interface iAuditQuestionCategory
	{
		// Accessors
			function get_category();
			
		// Mutators
			function set_id($value);
			function set_category($value);
			function set_sub_category_item($value);
			
		// Operators
			function xml();
	}
	
	class AuditQuestionCategory extends Common implements iAuditQuestionCategory
	{
		private
			$category		= NULL;
		
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
			
			if(is_array($this->category) === TRUE)			
			{
				
				foreach($this->category as $key => $id)
				{								
					$result .= '<row id="'.$id.'" />';									
				}			
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		// Accessors
		public function get_category()
		{
			return $this->category;
		}
		
		// Mutators
		// "sub" mutators prevent multiple instances of the same
		// data member name from different classes on a form
		// from interfering with each other.
		public function set_category($value)
		{
		}
		
		public function set_id($value)
		{
		}
			
		public function set_sub_category_item($value)
		{
			$this->category = $value;		
		}
	}	
?>