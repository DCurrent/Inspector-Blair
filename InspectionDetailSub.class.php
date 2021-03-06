<?php

	namespace data;

	interface iInspectionDetailSub
	{
		function xml();
		
		// Accessors
		function get_complete();
		function get_correction();		
		
		// Mutators
		function set_id($value);
		function set_label($value);
		function set_details($value);
		function set_sub_detail_id($value);	
		function set_sub_detail_label($value);	
		function set_sub_detail_details($value);	
		function set_sub_detail_complete($value);	
		function set_sub_detail_correction($value);		
	}	

	class InspectionDetailSub extends InspectionDetail implements iInspectionDetailSub
	{	
		protected
			$correction			= NULL,
			$complete			= NULL;
		
		public function xml()
		{
			$result = NULL;
			
			if(is_array($this->id))
			{			
				$result = '<root>';
							
				foreach($this->id as $key => $id)
				{							
					$result .= '<row id="'.$id.'">';				
					$result .= '<label>'.$this->label[$key].'</label>';
					
					// Details might not be sent (if unchecked). Make sure
					// to send an empty string.
					if(array_key_exists($key, $this->details ))
					{
						$result .= '<details>'.htmlspecialchars($this->details[$key]).'</details>';
					}
					else
					{
						$result .= '<details></details>';
					}
					
					$result .= '<correction>'.$this->correction[$key].'</correction>';
					
					
					// HTML radio buttons cannot be part of the 
					// same array like other inputs since the name is what
					// locks a group of radio buttons together. 
					// That means we have to identify each set of radio
					// buttons as a unique request and break them down
					// here.
					$complete = NULL;
					
					if(isset($_REQUEST['sub_detail_complete_'.$id]))
					{
						$complete = $_REQUEST['sub_detail_complete_'.$id];
					}
							
					$result .= '<complete>'.$complete.'</complete>';
					
					$result .= '</row>';									
				}
				
				$result .= '</root>';
			}
			
			return $result;
		}
		
		
		public function get_complete()
		{
			return $this->complete;
		}
		
		public function get_correction()
		{
			return $this->correction;
		}
		
		// To avoid data conflict
		public function set_id($value){}
		public function set_label($value){}
		public function set_details($value){}
		
		public function set_sub_detail_id($value)
		{
			return $this->id = $value;
		}
		
		public function set_sub_detail_label($value)
		{
			return $this->label = $value;
		}
		
		public function set_sub_detail_details($value)
		{
			return $this->details = $value;
		}
		
		public function set_sub_detail_complete($value)
		{
			return $this->complete = $value;
		}
		
		public function set_sub_detail_correction($value)
		{
			return $this->correction = $value;
		}
	}

?>