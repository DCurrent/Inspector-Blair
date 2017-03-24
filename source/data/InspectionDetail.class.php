<?php

	namespace data;

	interface iInspectionDetail
	{
		// Accessors
		function get_complete();
		function get_correction();		
		
		// Mutators
		function set_complete($value);	
		function set_correction($value);		
	}	

	class InspectionDetail extends Common implements iInspectionDetail
	{	
		protected
			$correction			= NULL,
			$complete			= NULL;
		
		public function get_complete()
		{
			return $this->complete;
		}
		
		public function get_correction()
		{
			return $this->correction;
		}
		
		public function set_complete($value)
		{
			return $this->complete = $value;
		}
		
		public function set_correction($value)
		{
			return $this->correction = $value;
		}
	}

?>