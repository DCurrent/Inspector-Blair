<?php

	namespace data;

	interface iInspectionVisit
	{
		// Accessors
		function get_visit_by();
		function get_visit_type();
		function get_time_recorded();		
		
		// Mutators
		function set_visit_by($value);	
		function set_visit_type($value);
		function set_time_recorded($value);		
	}	

	class InspectionVisit extends Common implements iInspectionVisit
	{	
		protected
			$visit_by			= NULL,
			$visit_type			= NULL,
			$time_recorded		= NULL;
		
		public function get_visit_by()
		{
			return $this->visit_by;
		}
		
		public function get_visit_type()
		{
			return $this->visit_type;
		}
		
		public function get_time_recorded()
		{
			return $this->time_recorded;
		}
		
		public function set_visit_by($value)
		{
			return $this->visit_by = $value;
		}
		
		public function set_visit_type($value)
		{
			return $this->visit_type = $value;
		}
		
		public function set_time_recorded($value)
		{
			return $this->time_recorded = $value;
		}
	}

?>