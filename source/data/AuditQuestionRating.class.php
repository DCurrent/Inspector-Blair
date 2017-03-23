<?php

	namespace data;
	
	interface iAuditQuestionRating
	{
		// Accessors
			function get_rating();
			
		// Mutators
			function set_id($value);
			function set_rating($value);
			function set_sub_rating_item($value);
			
		// Operators
			function xml();
	}
	
	class AuditQuestionRating extends Common implements iAuditQuestionRating
	{
		private
			$rating		= NULL;
		
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
			
			if(is_array($this->rating) === TRUE)			
			{
				
				foreach($this->rating as $key => $id)
				{								
					$result .= '<row id="'.$id.'" />';									
				}			
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		// Accessors
		public function get_rating()
		{
			return $this->rating;
		}
		
		// Mutators
		// "sub" mutators prevent multiple instances of the same
		// data member name from different classes on a form
		// from interfering with each other.
		public function set_rating($value)
		{
		}
		
		public function set_id($value)
		{
		}
			
		public function set_sub_rating_item($value)
		{
			$this->rating = $value;		
		}
	}	
?>