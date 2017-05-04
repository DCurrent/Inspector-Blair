<?php

	namespace data;

	interface iInspectionVisitSub
	{
		function xml();
		
		// Mutators
		function set_sub_visit_by($value);	
		function set_sub_visit_type($value);
		function set_sub_visit_time_recorded($value);	
		
		function set_id($value);		
		function set_details($value);		
		function set_label($value);
		function set_visit_time_recorded($value);	
	}	

	class InspectionVisitSub extends Inspection implements iInspectionVisitSub
	{	
		protected
			$visit_by			= NULL,
			$visit_type			= NULL,
			$time_recorded		= NULL;
			
		public function xml()
		{
			$result = NULL;
			
			if(is_array($this->id))
			{
			
				$result = '<root>';
							
				foreach($this->id as $key => $id)
				{	
					// Can't send blank guids.
					$visit_by 	= $this->visit_by[$key];
					$visit_type	= $this->visit_type[$key];
						
					$result .= '<row id="'.$id.'">';				
					$result .= '<label>'.$this->label[$key].'</label>';
					$result .= '<details>'.htmlspecialchars($this->details[$key]).'</details>';
					$result .= '<visit_by>'.$visit_by.'</visit_by>';
					$result .= '<visit_type>'.$visit_type.'</visit_type>';
					$result .= '<time_recorded>'.$this->time_recorded[$key].'</time_recorded>';
					$result .= '</row>';									
				}
				
				$result .= '</root>';
			}
			
			return $result;
		}
		
		public function set_id($value){}		
		public function set_details($value){}		
		public function set_label($value){}
		public function set_visit_time_recorded($value){}
		
		public function set_sub_visit_id($value)
		{
			return $this->id = $value;
		}
				
		public function set_sub_visit_by($value)
		{
			return $this->visit_by = $value;
		}
		
		public function set_sub_visit_type($value)
		{
			return $this->visit_type = $value;
		}
		
		public function set_sub_visit_time_recorded($value)
		{
			return $this->time_recorded = $value;
		}
	}

?>