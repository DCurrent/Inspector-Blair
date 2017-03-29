<?php

	namespace data;

	interface iInspection
	{	
		function get_status();		
		function get_status_label();		
		function get_inspection_type();		
		function get_inspection_type_label();
		
		// Mutators	
		function set_status($value);		
		function set_inspection_type($value);	
	}	

	class Inspection extends Common implements iInspection
	{	
		protected
			$status				= NULL,
			$status_label		= NULL,
			$inspection_type	= NULL,
			$inspection_type_label = NULL;
		
		// Accessors
		public function get_status()
		{
			return $this->status;
		}	
		
		public function get_status_label()
		{
			return $this->status_label;
		}
		
		public function get_inspection_type()
		{
			return $this->inspection_type;
		}
		
		public function get_inspection_type_label()
		{
			return $this->inspection_type_label;
		}
		
		// Mutators.		
		public function set_status($value)
		{
			$this->status = $value;
		}
		
		public function set_inspection_type($value)
		{
			$this->inspection_type = $value;
		}
	}

?>