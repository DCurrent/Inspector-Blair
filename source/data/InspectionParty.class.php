<?php

namespace data;

interface iInspectionParty
{
	// Get and return an xml string for database use.
	function xml();
	
	// Accessors
	function get_party();	
	
	// Mutators
	function set_sub_party_party($value);

}

class InspectionParty extends Common implements iInspectionParty
{
	private
		$party	= NULL;
	
	// Get and return an xml string for database use.
	// Get and return an xml string for database use.
	public function xml()
	{
		$result = '<root>';
		
		if(is_array($this->party) === TRUE)			
		{
			
			foreach($this->party as $key => $id)
			{								
				$result .= '<row id="'.$id.'" />';									
			}			
		}
		
		$result .= '</root>';
		
		return $result;
	}
	
	// Accessors
	public function get_party()
	{
		return $this->party;
	}
	
	// Mutators
	public function set_sub_party_party($value)
	{
		$this->party = $value;
	}
}

?>