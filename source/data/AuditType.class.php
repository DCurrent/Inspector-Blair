<?php

	namespace data;

	interface iAuditType
	{
		// Accessors
		function get_sort_order();
		
		// Mutators
		function set_sort_order($value);
	}

	class AuditType extends \data\Common implements iAuditType
	{
		protected $sort_order;
			
		// Accessors
		public function get_sort_order()
		{
			return $this->sort_order;
		}
		
		// Mutators
		public function set_sort_order($value)
		{
			if(!is_numeric($value))
			{
				$value = 0;
			}
			
			$this->sort_order = $value;
		}
	}

?>