<?php

	namespace data;

	interface iInspection
	{	
		function get_status();		
		function get_status_label();		
		function get_inspection_type();		
		function get_inspection_type_label();
		function get_visit_by_list();
		function get_visit_by_list_label();
		
		// Mutators	
		function set_status($value);		
		function set_inspection_type($value);
	}	

	class Inspection extends Area implements iInspection
	{	
		protected $status					= NULL;
		protected $status_label				= NULL;
		protected $inspection_type			= NULL;
		protected $inspection_type_label	= NULL;
		protected $visit_by_list			= NULL;
		protected $visit_by_list_label		= NULL;
		
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
		
		public function get_visit_by_list()
		{
			return $this->visit_by_list;
		}
		
		public function get_visit_by_list_label()
		{
			return $this->visit_by_list_label;
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