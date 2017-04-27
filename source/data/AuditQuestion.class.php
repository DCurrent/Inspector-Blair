<?php

	namespace data;

	interface iAuditQuestion
	{
		// Accessors
		function get_corrective_action();
		function get_finding();
		
		// Mutators
		function set_corrective_action($value);
		function set_finding($value); 
	}

	class AuditQuestion extends \data\Common implements iAuditQuestion
	{
		protected
			$finding 			= NULL,
			$corrective_action	= NULL;
			
		// Accessors
		public function get_corrective_action()
		{
			return $this->corrective_action;
		}
		
		public function get_finding()
		{
			return $this->finding;
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
	}

?>