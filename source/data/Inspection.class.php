<?php

	namespace data;

	interface iInspection
	{
		function get_room_id();		
		function get_room_code();		
		function get_building_code();		
		function get_building_name();		
		function get_status();		
		function get_status_label();		
		function get_inspection_type();		
		function get_inspection_type_label();		
		function get_radiation_usage();
		
		// Mutators
		function set_room_code($value);				
		function set_location($value);		
		function set_status($value);		
		function set_inspection_type($value);		
		function set_inspection_type_label($value);		
	}	

	class Inspection extends Common implements iInspection
	{	
		protected
			$room_id			= NULL,
			$room_code			= NULL,
			$building_code		= NULL,
			$building_name		= NULL,
			$status				= NULL,
			$status_label		= NULL,
			$inspection_type	= NULL,
			$inspection_type_label = NULL,
			$radiation_usage	= NULL;
		
		public function get_room_id()
		{
			return $this->room_id;
		}
		
		public function get_room_code()
		{
			return $this->room_code;
		}
		
		public function get_building_code()
		{
			return $this->building_code;
		}
		
		public function get_building_name()
		{
			return $this->building_name;
		}
		
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
		
		public function get_radiation_usage()
		{
			return $this->radiation_usage();
		}
		
		// Mutators
		public function set_room_code($value)
		{
			$this->room_code = $value;
		}
					
		public function set_location($value)
		{
			$this->location = $value;
		}
		
		public function set_status($value)
		{
			$this->status = $value;
		}	
		
		public function set_inspection_type($value)
		{
			$this->inspection_type = $value;
		}
		
		public function set_inspection_type_label($value)
		{
			$this->inspection_type_label = $value;
		}
	}

?>