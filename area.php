<?php 
	
	require(__DIR__.'/source/main.php');
	require(__DIR__.'/source/common_functions/common_check_action.php');
	require(__DIR__.'/source/common_functions/common_security.php');
	
	$_page_config_config = new \dc\application\CommonEntry($yukon_connection);	
	$_page_config = new \dc\application\CommonEntryConfig($_page_config_config);
	
	$_layout = $_page_config->create_config_object();

	const LOCAL_STORED_PROC_NAME 	= 'area'; 	// Used to call stored procedures for the main record set of this script.
	const LOCAL_BASE_TITLE 			= 'Area';	// Title display, button labels, instruction inserts, etc.
	$primary_data_class				= '\data\Area';
	
	// Delete current record.
	function action_delete($_layout = NULL)
	{
		// Set up account info.
		$access_obj = new \dc\access\status();
		
		// Initialize database query object.
		$query 	= new \dc\yukon\Database($yukon_connection);
		
		// Initialize main data class and populate it from
		// post variables. All we need is the ID, so
		// common data will work here.
		$_main_data = new \data\Common();						
		$_main_data->populate_from_request();
			
		// Call and execute delete SP.
		$query->set_sql('{call master_delete(@id = ?,													 
								@update_by	= ?, 
								@update_ip 	= ?)}');
		
		$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 			SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
		
					
		$query->set_params($params);
		$query->query_run();	
		
		// Refrsh page.
		header('Location: '.$_SERVER['PHP_SELF']);
		
	}

	// common_list
	// Caskey, Damon V.
	// 2017-02-22
	//
	// Switch to list mode for a record. Verifies the list
	// mode file exists first.
	function action_list($_layout = NULL)
	{				
		// Final result, and the target forwarding destination.
		$result 	= '#';
	
		// First thing we need is the self path.				
		$file = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
		
		// List giles are the name of a single record file
		// with _list added on, so all we need to do is
		// remove the file suffix, and add '_list.php' to
		// get the list file's name. This is also all we
		// need for forwarding purposes.	
		$target_name	= basename($file, '.php').'_list.php';		
		
		// To verify the list file exists, we have to target the
		// file system path. We can combine the document root
		// and self's directory to get it.
		$root			= filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_URL);
		$directory 		= dirname($file);
		//$target_file	= $root.$directory.'/'.$target_name;		
		$target_file	= $root.$directory.'/';
		
		// Does the list file exisit? If so we can
		// redirect to it. Otherwise, do nothing.
		if(file_exists($target_file))
		{	
			// Set target url.					
			$result = $target_name;			
		
			// Direct to listing.				
			header('Location: '.$result.'?id_form='.$_layout->get_id());
		}
		
		// Return final result. 
		return $result;
	}

	// Save this record.
	function action_save()
	{		
		// Initialize database query object.
		$query 	= new \dc\yukon\Database($yukon_connection);
		
		// Set up account info.
		$access_obj = new \dc\access\status();
				
		// Initialize main data class and populate it from
		// post variables.
		$_main_data = new \data\Area();						
		$_main_data->populate_from_request();
		
		$_type_data = new \data\AreaType();
		$_type_data->populate_from_request();
			
		$_biological_agent_data = new \data\BiologicalAgentSub();
		$_biological_agent_data->populate_from_request();
		
		// Call update stored procedure.
		$query->set_sql('{call '.LOCAL_STORED_PROC_NAME.'_update(@id			= ?,
												@log_update_by	= ?, 
												@log_update_ip 	= ?,										 
												@label 			= ?,
												@details 		= ?,
												@code			= ?,
												@param_type		= ?,
												@param_biological_agent = ?,
												@param_radiation_usage = ?,
												@param_laser_usage = ?,
												@param_x_ray_usage = ?,
												@param_chemical_operations_class = ?,
												@param_chemical_lab_class = ?,
												@param_ibc_protocol = ?,
												@param_biosafety_level = ?,
												@param_lab_unit_class = ?,
												@param_hazardous_waste_generated = ?)}');
												
		$params = array(array('<root><row id="'.$_main_data->get_id().'"/></root>', 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 				SQLSRV_PARAM_IN),
					array($_main_data->get_label(), 			SQLSRV_PARAM_IN),						
					array($_main_data->get_details(),			SQLSRV_PARAM_IN),
					array($_main_data->get_room_code(),			SQLSRV_PARAM_IN),
					array($_type_data->xml(),					SQLSRV_PARAM_IN),
					array($_biological_agent_data->xml(),		SQLSRV_PARAM_IN),
					array($_main_data->get_radiation_usage(),	SQLSRV_PARAM_IN),
					array($_main_data->get_laser_usage(),		SQLSRV_PARAM_IN),
					array($_main_data->get_x_ray_usage(),		SQLSRV_PARAM_IN),
					array($_main_data->get_chemical_operations_class(),		SQLSRV_PARAM_IN),
					array($_main_data->get_chemical_lab_class(),		SQLSRV_PARAM_IN),
					array($_main_data->get_ibc_protocol(),		SQLSRV_PARAM_IN),
					array($_main_data->get_biosafety_level(),		SQLSRV_PARAM_IN),
					array($_main_data->get_nfpa45_lab_unit(),		SQLSRV_PARAM_IN),
					array($_main_data->get_hazardous_waste_generated(),		SQLSRV_PARAM_IN));
		
		//var_dump($params);
		//exit;
		
		$query->set_params($params);			
		$query->query_run();
		
		// Repopulate main data object with results from merge query.
		// We can use common data here because all we need
		// is the ID for redirection.
		$query->get_line_config()->set_class_name('\data\Common');
		$_main_data = $query->get_line_object();
		
		// Now that save operation has completed, reload page using ID from
		// database. This ensures the ID is always up to date, even with a new
		// or copied record.
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id()); 
	}
	
	// Verify user access.
	common_security($yukon_database);
		
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
	
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();	
	
	// Record navigation - also gets user record action requests.
	$obj_navigation_rec = new \dc\recordnav\RecordNav();
	
	// Apply user action request (if any). Depending on the
	// action, the page may be reloaded with the same or
	// another ID.
	switch($obj_navigation_rec->get_action())
	{		
		default:		
		case \dc\recordnav\COMMANDS::NEW_BLANK:
			break;
			
		case \dc\recordnav\COMMANDS::LISTING:
							
			action_list($_layout);
			break;
			
		case \dc\recordnav\COMMANDS::DELETE:						
			
			action_delete($_layout);	
			break;				
					
		case \dc\recordnav\COMMANDS::SAVE:
			
			action_save($_layout);			
			break;			
	}

	// Last thing to do before moving on to main html is to get data to populate objects that
	// will then be used to generate forms and subforms. This may have already been done, 
	// such as when making copies of a record, but normally only a only blank object 
	// will exist at this point. We run a basic select query from our current ID and 
	// if a row is found overwrite whatever is in the main data object. If needed, we
	// repeat the process for any sub queries and forms.
	//
	// If there is no row at all found, nothing will be done - this is intended behavior because
	// there could be several reasons why no record is found here and we don't want to have 
	// overly complex or repetitive logic, but that does mean we have to make sure there
	// has been an object established at some point above.
	
	// Initialize database query object.
	$query 	= new \dc\yukon\Database($yukon_connection);
	
	// Initialize a blank main data object.
	$_main_data = new $primary_data_class();	
		
	// Populate from request so that we have an 
	// ID and KEY ID (if nessesary) to work with.
	$_main_data->populate_from_request();
	
	// Set up primary query with parameters and arguments.
	$query->set_sql('{call '.LOCAL_STORED_PROC_NAME.'(@param_filter_id = ?,
									@param_filter_id_key 	= ?,
									@param_filter_room_code = ?)}');
	$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($_main_data->get_id_key(), 	SQLSRV_PARAM_IN),
				   array($_main_data->get_room_code(), 	SQLSRV_PARAM_IN));

	// Apply arguments and execute query.
	$query->set_params($params);
	$query->query_run();
	
	// Get navigation record set and populate navigation object.		
	$query->get_line_config()->set_class_name('\dc\recordnav\RecordNav');	
	if($query->get_row_exists() === TRUE) $obj_navigation_rec = $query->get_line_object();	
	
	// Get primary data record set.	
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name($primary_data_class);	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	// Biological Agents (Taken from main query).
		$_obj_data_sub_agent_list = new \data\BiologicalAgentSub();
	
		$query->get_next_result();
		
		$query->get_line_config()->set_class_name('\data\BiologicalAgentSub');
		
		$_obj_data_sub_agent_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE)	$_obj_data_sub_agent_list = $query->get_line_object_list();		
	
	// Source lists
	
		// Types (Taken from main query)
		$_obj_field_source_type_list = new \data\AreaType();
	
		$query->get_next_result();
		
		$query->get_line_config()->set_class_name('\data\AreaType');
		
		$_obj_field_source_type_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE) $_obj_field_source_type_list = $query->get_line_object_list();
			
		// Biological agents
			$_obj_field_source_agent_list = new \data\Common();
		
			$query->set_sql('{call biological_agent_list(@param_page_current = ?)}');
			$query->set_params(array(-1));
			
			$query->query_run();
			$query->get_line_config()->set_class_name('\data\Common');
			
			$_obj_field_source_agent_list = new SplDoublyLinkedList();
			if($query->get_row_exists() === TRUE)
			{
				$_obj_field_source_agent_list = $query->get_line_object_list();
			}
			
			// Generate a list for new record insert. List for existing records are generated per each
			// record loop to 'select' the current record value.
			$host_list_new = NULL;
					
			for($_obj_field_source_agent_list->rewind();	$_obj_field_source_agent_list->valid(); $_obj_field_source_agent_list->next())
			{	                                                               
				$_obj_field_source_agent_current = $_obj_field_source_agent_list->current();					
				
				$host_list_new .= '<option value="'.$_obj_field_source_agent_current->get_id().'">'.$_obj_field_source_agent_current->get_label().'</option>';					
			}
		
		// Chemical operations
			$_obj_field_source_chemical_operations_list = new \data\Common();
		
			$query->set_sql('{call chemical_operations_class_list(@param_page_current = ?)}');
			$query->set_params(array(-1));
			
			$query->query_run();
			$query->get_line_config()->set_class_name('\data\Common');
			
			$_obj_field_source_chemical_operations_list = new SplDoublyLinkedList();
			if($query->get_row_exists() === TRUE)
			{
				$_obj_field_source_chemical_operations_list = $query->get_line_object_list();
			}
			
		// Chemical Lab
			//$_obj_field_source_chemical_lab_list = new \data\Common();
		
			//$query->set_sql('{call chemical_operations_lab_list(@param_page_current = ?)}');
			//$query->set_params(array(-1));
			
			//$query->query_run();
			//$query->get_line_config()->set_class_name('\data\Common');
			
			//$_obj_field_source_chemical_lab_list = new SplDoublyLinkedList();
			
			//$_obj_field_source_chemical_lab_list->push(1);
			//$_obj_field_source_chemical_lab_list->push(2);
			//$_obj_field_source_chemical_lab_list->push(3);
			//$_obj_field_source_chemical_lab_list->push(4);
			
			//if($query->get_row_exists() === TRUE)
			//{
			//	$_obj_field_source_chemical_lab_list = $query->get_line_object_list();
			//}
			
		// Biosafety Level
			$_obj_field_source_biosafety_level_list = new \data\Common();
		
			$query->set_sql('{call biosafety_level_list(@param_page_current = ?)}');
			$query->set_params(array(-1));
			
			$query->query_run();
			$query->get_line_config()->set_class_name('\data\Common');
			
			$_obj_field_source_biosafety_level_list = new SplDoublyLinkedList();
			
			if($query->get_row_exists() === TRUE)
			{
				$_obj_field_source_biosafety_level_list = $query->get_line_object_list();
			}

	
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME. ', '.LOCAL_BASE_TITLE; ?></title>        
        
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <style>
						
			.incident {
				font-size:larger;			
			}
			
			ul.checkbox  { 
				
			 	-webkit-column-count: auto;  				
				-moz-column-count: auto;				
			  column-count: auto;			 
			  margin: 0; 
			  padding: 0; 
			  margin-left: 20px; 
			  list-style: none;			  
			} 
			
			ul.checkbox li input { 
			  margin-right: .25em; 
			  cursor:pointer;
			} 
			
			ul.checkbox li { 
			  border: 1px transparent solid; 
			  display:inline-block;
			  width:12em;			  
			} 
			
			ul.checkbox li label { 
			  margin-left: ;
			  cursor:pointer;			  
			} 
			
		</style>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>     
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>        
        
        <!-- WYSIWYG Text boxes -->
		<script type="text/javascript" src="source/javascript/tinymce/tinymce.min.js"></script>
        <script type="text/javascript" src="source/javascript/tinymce/settings.js"></script>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $obj_navigation_main->generate_markup_nav(); ?>                                                                                
            <div class="page-header">           
                <h1><?php echo LOCAL_BASE_TITLE; ?></h1>
                <p class="lead">This screen allows for adding and editing <?php echo LOCAL_BASE_TITLE; ?> records.</p>
                <?php require(__DIR__.'/source/common_includes/revision_alert.php'); ?>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->generate_button_list(); ?>
                <hr />
                
                <?php require(__DIR__.'/source/common_includes/details_field.php'); ?>
                
             	<div class="form-group">       
                    <label class="control-label col-sm-2" for="revision">Revision</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"> 
                        <?php 	
								// If this is a new or non-exisiting record, alert the user.
								if(!($_main_data->get_id() < 0))
								{
								?>
                                <a id="revision" href = "common_version_list.php?id=<?php echo $_main_data->get_id();  ?>"
                                                            data-toggle	= ""
                                                            title		= "View log for this record."
                                                             target		= "_new" 
                            	><?php  echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_main_data->get_create_time()->getTimestamp()); ?></a>
                        		<?php
								}
								else
								{
								?>
                                	<span class="alert-success">New or unsaved record. Fill out form and save to create first revision.</span>
                                <?php
								}
								?>
                                
                    	</p>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="building">Building</label>
                    <div class="col-sm-10">
                        <?php 
							// Not a new GUID? Then echo the data.
							if($_main_data->get_building_code())
							{
								echo $_main_data->get_building_code().' - '.$_main_data->get_building_name(); 
							}
						?>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="floor">Floor</label>
                    <div class="col-sm-10">
                        <?php echo trim($_main_data->get_floor()); ?>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="room">Area/Room</label>
                    <div class="col-sm-10">
                    	<?php 
							// Not a new GUID? Then echo the data.
							if($_main_data->get_room_code())
							{
								echo trim($_main_data->get_room_id());
								
								// Add description if it is available.
								if($_main_data->get_use_description_short())
								{
									echo ' - '.$_main_data->get_use_description_short();
								}				
							}
						?>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="code">Room Code</label>
                    <div class="col-sm-10">
                        <input 
                            type	="text" 
                            class	="form-control"  
                            name	="room_code" 
                            id		="room_code" 
                            placeholder="Room bar code." 
                            value="<?php echo trim($_main_data->get_room_code()); ?>">
                    </div>
                </div> 
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="label">Label</label>
                    <div class="col-sm-10">
                        <input 
                            type	="text" 
                            class	="form-control"  
                            name	="label" 
                            id		="label" 
                            placeholder="Room label." 
                            value="<?php echo trim($_main_data->get_label()); ?>">
                    </div>
                </div>        
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="chemical_operations_class">Chemical Operations Class</label>
                    <div class="col-sm-10">
                    	<select
                            name 	= "chemical_operations_class"
                            id		= "chemical_operations_class"
                            class	= "form-control">
                            <option value = "">None</option>
                            <?php 
								
								// Generate a list for new record insert. List for existing records are generated per each
								// record loop to 'select' the current record value.
								$selected = NULL;
										
								for($_obj_field_source_chemical_operations_list->rewind();	$_obj_field_source_chemical_operations_list->valid(); $_obj_field_source_chemical_operations_list->next())
								{	                                                               
									$_obj_field_source_chemical_operations_current = $_obj_field_source_chemical_operations_list->current();					
									
									if($_main_data->get_chemical_operations_class() == $_obj_field_source_chemical_operations_current->get_id())
									{
										$selected = 'selected';
									}
									else
									{
										$selected = NULL;
									}
									
									?>
									
									<option value="<?php echo $_obj_field_source_chemical_operations_current->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_field_source_chemical_operations_current->get_label(); ?></option>;					
									<?php
                                }
							
							?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="chemical_lab_class"><a href="./?id_form=1170&amp;list=1" target="_new">Chemical Lab Class</a></label>
                    <div class="col-sm-10">
                    	<select
                            name 	= "chemical_lab_class"
                            id		= "chemical_lab_class"
                            class	= "form-control">
                            <option value = "">None</option>
                            <?php 
								
								// Generate a list for new record insert. List for existing records are generated per each
								// record loop to 'select' the current record value.
								$selected = NULL;
										
								for($_obj_field_source_chemical_operations_list->rewind();	$_obj_field_source_chemical_operations_list->valid(); $_obj_field_source_chemical_operations_list->next())
								{	                                                               
									$_obj_field_source_chemical_operations_current = $_obj_field_source_chemical_operations_list->current();					
									
									if($_main_data->get_chemical_lab_class() == $_obj_field_source_chemical_operations_current->get_id())
									{
										$selected = 'selected';
									}
									else
									{
										$selected = NULL;
									}
									
									?>
									
									<option value="<?php echo $_obj_field_source_chemical_operations_current->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_field_source_chemical_operations_current->get_label(); ?></option>;					
									<?php
                                }
							
							?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="nfpa45_lab_unit"><a href="./?id_form=1608&amp;list=1" target="_new">NFPA45 Lab Unit</a></label>
                    <div class="col-sm-10">
                    	<select
                            name 	= "nfpa45_lab_unit"
                            id		= "nfpa45_lab_unit"
                            class	= "form-control">
                            <option value = "">None</option>
                            <?php 
								
								// Generate a list for new record insert. List for existing records are generated per each
								// record loop to 'select' the current record value.
								$selected = NULL;
										
								for($_obj_field_source_chemical_operations_list->rewind();	$_obj_field_source_chemical_operations_list->valid(); $_obj_field_source_chemical_operations_list->next())
								{	                                                               
									$_obj_field_source_chemical_operations_current = $_obj_field_source_chemical_operations_list->current();					
									
									if($_main_data->get_nfpa45_lab_unit() == $_obj_field_source_chemical_operations_current->get_id())
									{
										$selected = 'selected';
									}
									else
									{
										$selected = NULL;
									}
									
									?>
									
									<option value="<?php echo $_obj_field_source_chemical_operations_current->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_field_source_chemical_operations_current->get_label(); ?></option>;					
									<?php
                                }
							
							?>
                        </select>
                    </div>
                </div>
                
                <?php
					$select_radio_id	= 'radiation_usage';
					$select_radio_true 	= NULL;
					$select_radio_false = NULL;
					
					if($_main_data->get_radiation_usage())
					{
						$select_radio_true = 'checked="checked"';
					}
					else
					{
						$select_radio_false = 'checked="checked"';
					}
				?>
                
                <div class="form-group">
                    <label class="control-label col-sm-2" for="<?php echo $select_radio_id; ?>">Radiation Usage</label>
                    <div class="col-md-10">
                        <label class="radio-inline">
                            <input <?php echo $select_radio_true; ?> id="<?php echo $select_radio_id; ?>_1" name="<?php echo $select_radio_id; ?>" value="1" type="radio">
                            Yes
                        </label>
                        <label class="radio-inline">
                            <input <?php echo $select_radio_false; ?> id="<?php echo $select_radio_id; ?>_0" name="<?php echo $select_radio_id; ?>" value="0" type="radio">
                            No
                        </label>
                    </div>
                </div>
                
                <?php
					$select_radio_id	= 'laser_usage';
					$select_radio_true 	= NULL;
					$select_radio_false = NULL;
					
					if($_main_data->get_laser_usage())
					{
						$select_radio_true = 'checked="checked"';
					}
					else
					{
						$select_radio_false = 'checked="checked"';
					}
				?>
                
                <div class="form-group">
                    <label class="control-label col-sm-2" for="<?php echo $select_radio_id; ?>">Laser Usage</label>
                    <div class="col-md-10">
                        <label class="radio-inline">
                            <input <?php echo $select_radio_true; ?> id="<?php echo $select_radio_id; ?>_1" name="<?php echo $select_radio_id; ?>" value="1" type="radio">
                            Yes
                        </label>
                        <label class="radio-inline">
                            <input <?php echo $select_radio_false; ?> id="<?php echo $select_radio_id; ?>_0" name="<?php echo $select_radio_id; ?>" value="0" type="radio">
                            No
                        </label>
                    </div>
                </div>
                
                <?php
					$select_radio_id	= 'x_ray_usage';
					$select_radio_true 	= NULL;
					$select_radio_false = NULL;
					
					if($_main_data->get_x_ray_usage())
					{
						$select_radio_true = 'checked="checked"';
					}
					else
					{
						$select_radio_false = 'checked="checked"';
					}
				?>
                
                <div class="form-group">
                    <label class="control-label col-sm-2" for="<?php echo $select_radio_id; ?>">X-Ray Usage</label>
                    <div class="col-md-10">
                        <label class="radio-inline">
                            <input <?php echo $select_radio_true; ?> id="<?php echo $select_radio_id; ?>_1" name="<?php echo $select_radio_id; ?>" value="1" type="radio">
                            Yes
                        </label>
                        <label class="radio-inline">
                            <input <?php echo $select_radio_false; ?> id="<?php echo $select_radio_id; ?>_0" name="<?php echo $select_radio_id; ?>" value="0" type="radio">
                            No
                        </label>
                    </div>
                </div>   
                
                <?php
					$select_radio_id	= 'hazardous_waste_generated';
					$select_radio_true 	= NULL;
					$select_radio_false = NULL;
					
					if($_main_data->get_hazardous_waste_generated())
					{
						$select_radio_true = 'checked="checked"';
					}
					else
					{
						$select_radio_false = 'checked="checked"';
					}
				?>
                
                <div class="form-group">
                    <label class="control-label col-sm-2" for="<?php echo $select_radio_id; ?>">Hazardous Waste Generated</label>
                    <div class="col-md-10">
                        <label class="radio-inline">
                            <input <?php echo $select_radio_true; ?> id="<?php echo $select_radio_id; ?>_1" name="<?php echo $select_radio_id; ?>" value="1" type="radio">
                            Yes
                        </label>
                        <label class="radio-inline">
                            <input <?php echo $select_radio_false; ?> id="<?php echo $select_radio_id; ?>_0" name="<?php echo $select_radio_id; ?>" value="0" type="radio">
                            No
                        </label>
                    </div>
                </div>     
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="biosafety_level"><a href="./?id_form=1169&amp;list=1" target="_new">Biosafety Level</a></label>
                    <div class="col-sm-10">
                    	<select
                            name 	= "biosafety_level"
                            id		= "biosafety_level"
                            class	= "form-control">
                            <option value = "">None</option>
                            <?php 
								
								// Generate a list for new record insert. List for existing records are generated per each
								// record loop to 'select' the current record value.
								$selected = NULL;
										
								for($_obj_field_source_biosafety_level_list->rewind();	$_obj_field_source_biosafety_level_list->valid(); $_obj_field_source_biosafety_level_list->next())
								{	                                                               
									$_obj_field_source_biosafety_level_current = $_obj_field_source_biosafety_level_list->current();					
									
									if($_main_data->get_biosafety_level() == $_obj_field_source_biosafety_level_current->get_id())
									{
										$selected = 'selected';
									}
									else
									{
										$selected = NULL;
									}
									
									?>
									
									<option value="<?php echo $_obj_field_source_biosafety_level_current->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_field_source_biosafety_level_current->get_label(); ?></option>;					
									<?php
                                }
							
							?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="ibc_protocol">IBC Protocol</label>
                    <div class="col-sm-10">
                        <input 
                            type	="text" 
                            class	="form-control"  
                            name	="ibc_protocol" 
                            id		="ibc_protocol" 
                            placeholder="IBC Protocol #." 
                            value="<?php echo trim($_main_data->get_ibc_protocol()); ?>">
                    </div>
                </div>
                
                <div class="form-group" id="types-container">
                	<label class="control-label col-sm-2" for=""><a href="./?id_form=1185&amp;list=1" target="_new">Types</a></label>
                    <div class="col-sm-10">
                        <ul class="checkbox">
                        <?php                              
                                if(is_object($_obj_field_source_type_list) === TRUE)
                                {        
                                    // Generate table row for each item in list.
                                    for($_obj_field_source_type_list->rewind(); $_obj_field_source_type_list->valid(); $_obj_field_source_type_list->next())
                                    {						
                                        $_obj_field_source_type = $_obj_field_source_type_list->current();
										
										$selected = NULL;
										
										// Selected will be the ID that came from source table - but we really don't care. Any value will
										// mean this is a selected item.
										if($_obj_field_source_type->get_selected())
										{
											$selected = ' checked ';
										}
                                                               
                                    ?>
                                        <li>                                       
                                            <input 
                                                type	= "checkbox" 
                                                id		= "sub_type_<?php echo $_obj_field_source_type->get_id(); ?>"
                                                name	= "sub_type[]" 
                                                value	= "<?php echo $_obj_field_source_type->get_id(); ?>"
                                                <?php echo $selected; ?> />
                                                <label 
                                                    class="checkbox-inline" 
                                                    for = "sub_type_<?php echo $_obj_field_source_type->get_id(); ?>"><?php echo $_obj_field_source_type->get_label(); ?></label>
                                        </li>                                   
                                <?php
                                    }
                                }
                                ?> 
                        </ul>
                    </div>
                </div><!-- #types-container -->
                
                <div class="form-group" id="agents_container">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="./biological_agent_list.php" target="_new">Biological Agents</a></legend>                    
      						
                            <table class="table table-striped table-hover" id="POITable"> 
                                <thead>
                                    <tr>
                                        <th colspan="2"><a href="./biological_agent_list.php" target="_new">Agent</a></th> 
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_agent_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_agent_list->rewind(); $_obj_data_sub_agent_list->valid(); $_obj_data_sub_agent_list->next())
										{						
											$_obj_data_sub_current = $_obj_data_sub_agent_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub_current->get_id()) $_obj_data_sub_current->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_agent_agent[]"
                                                        id		= "sub_agent_agent_<?php echo $_obj_data_sub_current->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
														echo '<!--get_item(): '.$_obj_data_sub_current->get_item().'-->';
                                                        if(is_object($_obj_field_source_agent_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_agent_list->rewind();	$_obj_field_source_agent_list->valid(); $_obj_field_source_agent_list->next())
                                                            {	                                                               
																$_obj_field_source_agent = $_obj_field_source_agent_list->current();
																
																$sub_role_value 	= $_obj_field_source_agent->get_id();																
																$sub_role_label		= $_obj_field_source_agent->get_label();
																$sub_role_selected 	= NULL;
																
																if($_obj_data_sub_current->get_item() === $sub_role_value) $sub_role_selected = ' selected '
																
																?>
                                                                <option 
                                                                	value="<?php echo $sub_role_value; ?>" 
																	<?php echo $sub_role_selected ?>><?php echo $sub_role_label; ?></option>
                                                                
																<?php
																
                                                            }
                                                        }
													?>
                                                    </select> 
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_agent_id[]" 
														id		="sub_agent_id_<?php echo $_obj_data_sub_current->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub_current->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub_current->get_id(); ?>" 
														onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>        
												</td>
											</tr>                                    
									<?php
										}
									}
                                    ?>                        
                                </tbody>                        
                            </table>                      
                            
                            
                            <button 
                                type	="button" 
                                class 	="btn btn-success" 
                                name	="row_add" 
                                id		="row_add_perm"
                                title	="Add new item."
                                onclick	="insRow()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                    </div>                        
                </div><!-- #agents-container -->
                
                 
                <hr />
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>               
            </form>
            
            <?php echo $obj_navigation_main->generate_markup_footer(); ?>
        </div><!--container-->        
		<script src="source/javascript/verify_save.js"></script>
		<script>
            //Google Analytics Here// 
        
            $(document).ready(function(){
            });
        </script>
        <script src="source/javascript/dc_guid.js"></script>
		<script>
             
            function deleteRowsub(row)
            {
                var i=row.parentNode.parentNode.rowIndex;
                document.getElementById('POITable').deleteRow(i);
            }
            
            // Inserts a new table row on user request.
            function insRow()
            {
                var $guid = null;
                
                $guid = dc_guid();
                
                $(".ec").append(
                    '<tr>'
                        +'<td>'
                            +'<select name="sub_agent_agent[]" id="sub_agent_agent_'+ $guid +'" class="form-control">'
                            +'<?php echo $host_list_new; ?>'
                            +'</select>'																		
                        +'</td>'					
                        +'<td colspan="2">'
                            +'<input type="hidden" name="sub_agent_id[]" id="sub_agent_id_js_'+$guid+'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
                            +'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+$guid+'" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
                        +'</td>'
                    +'<tr>');
                
			}			
             
        </script>
	</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>