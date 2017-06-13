<?php

	namespace data;

	interface iAuditQuestion
	{
		// Accessors
		function get_corrective_action();
		function get_finding();
		function get_status();
		
		// Mutators
		function set_corrective_action($value);
		function set_finding($value); 
		function set_status($value);
	}

	class AuditQuestion extends \data\Common implements iAuditQuestion
	{
		protected
			$finding 			= NULL,
			$corrective_action	= NULL,
			$status				= NULL;
			
		// Accessors
		public function get_corrective_action()
		{
			return $this->corrective_action;
		}
		
		public function get_finding()
		{
			return $this->finding;
		}
		
		public function get_status()
		{
			return $this->status;
		}
		
		// Mutators
		public function set_corrective_action($value)
		{
			$this->corrective_action = $value;
		}
		
		public function set_finding($value)
		{
			$this->finding = $value;
		}		
		
		public function set_status($value)
		{	
			$this->status = $value;
		}		
	}

?>