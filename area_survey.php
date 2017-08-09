<?php
	require(__DIR__.'/source/main.php');

	class ConfigLocal extends data\Common
	{
		private $id_guid;
		private $filter_area_code;
		
		// Accessors
		public function get_id_guid()
		{
			return $this->id_guid;
		}
		
		public function get_filter_area_code()
		{
			return $this->filter_area_code;
		}
		
		// Mutators
		public function set_filter_area_code($value)
		{
			$this->filter_area_code = $value;
		}		
	}

	// Start page cache.
	$page_obj = new \dc\cache\PageCache();

	// Get request variables.
	$config = new ConfigLocal();
	$config->populate_from_request();

	$id_ext			= $config->get_id_guid();
	$option_list	= NULL;   	

	// --Accounts (Inspector)
	

	$yukon_database->set_sql('{call area_survey(@sort_field		= ?,
										@sort_order				= ?,
										@param_filter_id		= ?,
										@param_filter_id_key	= ?,
										@param_filter_code		= ?)}');

	$params = array(array(NULL, 							SQLSRV_PARAM_IN), 
					array(NULL, 							SQLSRV_PARAM_IN),
					array(NULL, 							SQLSRV_PARAM_IN),
					array(NULL, 							SQLSRV_PARAM_IN),
					array($config->get_filter_area_code(),	SQLSRV_PARAM_IN));

	$yukon_database->set_param_array($params);
	$yukon_database->query_run();

	$yukon_database->get_line_config()->set_class_name('\data\Area');

	$_obj_data_main = new \data\Area;
	if($yukon_database->get_row_exists() === TRUE) $_obj_data_main = $yukon_database->get_line_object();

	

	// Prepare human readable building code.
	$building_code_display = NULL;

	if($_obj_data_main->get_building_code())
	{
		$building_code_display = trim($_obj_data_main->get_building_code()).' - '.$_obj_data_main->get_building_name(); 
	}
?>

<table class="table table-striped table-condensed">
	<thead>
	</thead>
	<tfoot>
	</tfoot>
	<tbody id="tbody_room_data" class="">
		<tr>
			<td>Area</td>
			<td><a href = "area.php?id=<?php echo $_obj_data_main->get_room_code();  ?>"
			data-toggle	= ""
			title		= "View location detail."
			target		= "_new" 
			><?php echo $building_code_display; ?></a></td>
		</tr>

		<tr>
			<td>Biosafety Level</td>
			<td><?php 
				if($_obj_data_main->get_biosafety_level())  
				{									
					echo $_obj_data_main->get_biosafety_level();									
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?></td>
		</tr>

		<tr>
			<td>Chemical Lab Class</td>
			<td><?php 
				if($_obj_data_main->get_chemical_lab_class())  
				{									
					echo $_obj_data_main->get_chemical_lab_class();									
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?>                                    					
			</td>
		</tr>

		<tr>
			<td>Chemical Operations Class</td>
			<td><?php 
				if($_obj_data_main->get_chemical_operations_class())  
				{									
					echo $_obj_data_main->get_chemical_operations_class();									
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?></td>
		</tr>

		<tr>
			<td>Department</td>
			<td><?php echo $_obj_data_main->get_department();  ?></td>
		</tr>

		<tr>
			<td>Hazardous Waste</td>
			<td><?php 
				if($_obj_data_main->get_hazardous_waste_generated())  
				{
				?>									
					<span class="glyphicon glyphicon-ok alert-warning"></span>									
				<?php
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?></td>
		</tr>

		<tr>
			<td>Radiation Usage</td>
			<td><?php 
				if($_obj_data_main->get_radiation_usage())  
				{
				?>									
					<span class="glyphicon glyphicon-ok alert-warning"></span>									
				<?php
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?></td>
		</tr>
		<tr>
			<td>X-ray Usage</td>
			<td><?php 
				if($_obj_data_main->get_x_ray_usage())  
				{
				?>									
					<span class="glyphicon glyphicon-ok alert-warning"></span>									
				<?php
				}
				else
				{	
				?>
					<span class="glyphicon glyphicon-remove alert-info"></span>
				<?php
				}
				?></td>
		</tr>

	</tbody>
</table>


<?php
	// So the loading bar doesn't look like a glitch when loading is fast.
	sleep(1);

	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>