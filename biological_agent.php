<?php 
	
	require(__DIR__.'/source/main.php');
	require(__DIR__.'/source/common_functions/common_check_action.php');
	require(__DIR__.'/source/common_functions/common_security.php');
	
	const LOCAL_STORED_PROC_NAME 	= 'biological_agent'; 	// Used to call stored procedures for the main record set of this script.
	const LOCAL_BASE_TITLE 			= 'Biological Agent';	// Title display, button labels, instruction inserts, etc.
	$primary_data_class				= '\data\BiologicalAgent';
			
	// Save this record.
	function action_save()
	{		
		// Initialize database query object.
		$query 	= new \dc\yukon\Database();
		
		// Set up account info.
		$access_obj = new \dc\access\status();
				
		// Initialize main data class and populate it from
		// post variables.
		$_main_data = new \data\BiologicalAgent();						
		$_main_data->populate_from_request();
		
		$_host_data = new \data\BiologicalAgentHost();
		$_host_data->populate_from_request();
			
		// Call update stored procedure.
		$query->set_sql('{call '.LOCAL_STORED_PROC_NAME.'_update(@param_id			= ?,
												@param_log_update_by	= ?, 
												@param_log_update_ip 	= ?,										 
												@param_label 			= ?,
												@param_details 			= ?,
												@param_information		= ?,
												@param_risk_group		= ?,
												@param_host				= ?)}');
												
		$params = array(array('<root><row id="'.$_main_data->get_id().'"/></root>', 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 			SQLSRV_PARAM_IN),
					array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
					array($_main_data->get_details(),		SQLSRV_PARAM_IN),
					array($_main_data->get_information(),	SQLSRV_PARAM_IN),
					array($_main_data->get_risk_group(),	SQLSRV_PARAM_IN),
					array($_host_data->xml(),				SQLSRV_PARAM_IN));
		
		var_dump($params);
		//exit;
		
		$query->set_params($params);			
		$query->query();
		
		// Repopulate main data object with results from merge query.
		// We can use common data here because all we need
		// is the ID for redirection.
		$query->get_line_params()->set_class_name('\data\Common');
		$_main_data = $query->get_line_object();
		
		// Now that save operation has completed, reload page using ID from
		// database. This ensures the ID is always up to date, even with a new
		// or copied record.
		header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id()); 
	}
	
	// Verify user access.
	common_security();
		
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
	
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();	
	
	// Record navigation - also gets user record action requests.
	$obj_navigation_rec = new \dc\recordnav\RecordNav();
	
	// Apply user action request (if any). Depending on the
	// action, the page may be reloaded with the same or
	// another ID.
	common_check_action($obj_navigation_rec->get_action());
	
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
	$query 	= new \dc\yukon\Database();
	
	// Initialize a blank main data object.
	$_main_data = new $primary_data_class();	
		
	// Populate from request so that we have an 
	// ID and KEY ID (if nessesary) to work with.
	$_main_data->populate_from_request();
	
	// Set up primary query with parameters and arguments.
	$query->set_sql('{call '.LOCAL_STORED_PROC_NAME.'(@param_filter_id = ?,
									@param_filter_id_key = ?)}');
	$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($_main_data->get_id_key(), 	SQLSRV_PARAM_IN));

	// Apply arguments and execute query.
	$query->set_params($params);
	$query->query();
	
	// Get navigation record set and populate navigation object.		
	$query->get_line_params()->set_class_name('\dc\recordnav\RecordNav');	
	if($query->get_row_exists() === TRUE) $obj_navigation_rec = $query->get_line_object();	
	
	// Get primary data record set.	
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name($primary_data_class);	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	// Sub table (role) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\data\BiologicalAgentHost');
	
	$_obj_data_sub_host_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_host_list = $query->get_line_object_list();
	
	// Source lists
		// Hosts (Taken from main query)
		//$_obj_field_source_host_list = new \data\BiologicalAgentHost();
	
		//$query->get_next_result();
		
		//$query->get_line_params()->set_class_name('\data\BiologicalAgentHost');
		
		//$_obj_field_source_host_list = new SplDoublyLinkedList();
		//if($query->get_row_exists() === TRUE) $_obj_field_source_host_list = $query->get_line_object_list();
		
		$_obj_field_source_host_list = new \data\Common();
	
		$query->set_sql('{call biological_host_list(@param_page_current = ?)}');
		$query->set_params(array(-1));
		
		$query->query();
		$query->get_line_params()->set_class_name('\data\Common');
		
		$_obj_field_source_host_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE)
		{
			$_obj_field_source_host_list = $query->get_line_object_list();
		}
		
		// Generate a list for new record insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$host_list_new = NULL;
				
		for($_obj_field_source_host_list->rewind();	$_obj_field_source_host_list->valid(); $_obj_field_source_host_list->next())
		{	                                                               
			$_obj_field_source_host_current = $_obj_field_source_host_list->current();					
			
			$host_list_new .= '<option value="'.$_obj_field_source_host_current->get_id().'">'.$_obj_field_source_host_current->get_label().'</option>';					
		}
		
		// Risk groups
		$_obj_field_source_risk_group_list = new \data\Common();
	
		$query->set_sql('{call biological_risk_group_list(@param_page_current = ?)}');
		$query->set_params(array(-1));
		
		$query->query();
		$query->get_line_params()->set_class_name('\data\Common');
		
		$_obj_field_source_risk_group_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE)
		{
			$_obj_field_source_risk_group_list = $query->get_line_object_list();
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
				
			 	-webkit-column-count: 3;  				
				-moz-column-count: auto;				
			  column-count: 3;			 
			  margin: 10; 
			  padding: 10; 
			  margin-left: 20px; 
			  list-style: none;			  
			} 
			
			ul.checkbox li input { 
			  margin-right: 30px; 
			  cursor:pointer;
			  padding: 10;
			} 
			
			ul.checkbox li { 
			  border: 1px transparent solid; 
			  display:inline-block;
			  width:12em;			  
			} 
			
			ul.checkbox li label { 
			  margin-right: 10px;
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
                        <?php if(is_object($_main_data->get_create_time()))
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
                                	<span class="alert-success">New Record. Fill out form and save to create first revision.</span>
                                <?php
								}
								?>
                                
                    	</p>
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
                            placeholder="Agent label." 
                            value="<?php echo trim($_main_data->get_label()); ?>">
                    </div>
                </div> 
                
                 <div class="form-group">       
                    <label class="control-label col-sm-2" for="label">Information</label>
                    <div class="col-sm-10">
                    	<textarea class="form-control wysiwyg" rows="2" name="information" id="information"><?php echo $_main_data->get_information(); ?></textarea>                          
                    </div>
                </div>      
                
                <div class="form-group" id="risk-group-container">
                    <label class="control-label col-sm-2" for="risk_group"><a href="./?id_form=1168&amp;list=1" target="_new">Risk Group</a></label>
                    <div class="col-sm-10">
                        <select name="risk_group" 
                            id="risk_group" 
                            data-current="<?php echo $_main_data->get_risk_group(); ?>" 
                            data-grouped="1"
                            class="form-control">                   
                            <option value="">Select Risk Group</option>
                                <?php
                                
                                    // Generate table row for each item in list.
                                    for($_obj_field_source_risk_group_list->rewind();	$_obj_field_source_risk_group_list->valid(); $_obj_field_source_risk_group_list->next())
                                    {	 
										echo 'loop';                                                              
                                        $_obj_field_source_single = $_obj_field_source_risk_group_list->current();
                                        
                                        $sub_value 		= $_obj_field_source_single->get_id();		
                                        $sub_selected 	= NULL;
                                          
										if($_main_data->get_risk_group() == $sub_value)
										{
											$sub_selected = ' selected ';
										}		                                     
                                        
                                        ?>
                                        <option value="<?php echo $sub_value; ?>" <?php echo $sub_selected; ?>><?php echo $_obj_field_source_single->get_label(); ?></option>
                                        <?php                                
                                    }
                                
                                ?>                        
                        </select>
                    </div>                
                </div>   
                
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="./?id_form=1148&amp;list=1" target="_new">Hosts</a></legend>                    
      						
                            <table class="table table-striped table-hover" id="POITable"> 
                                <thead>
                                    <tr>
                                        <th colspan="2"><a href="./?id_form=1148&amp;list=1" target="_new">Host</a></th> 
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_host_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_host_list->rewind(); $_obj_data_sub_host_list->valid(); $_obj_data_sub_host_list->next())
										{						
											$_obj_data_sub_current = $_obj_data_sub_host_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub_current->get_id()) $_obj_data_sub_current->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_host_host[]"
                                                        id		= "sub_host_host_<?php echo $_obj_data_sub_current->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
														echo '<!--get_item(): '.$_obj_data_sub_current->get_item().'-->';
                                                        if(is_object($_obj_field_source_host_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_host_list->rewind();	$_obj_field_source_host_list->valid(); $_obj_field_source_host_list->next())
                                                            {	                                                               
																$_obj_field_source_host = $_obj_field_source_host_list->current();
																
																$sub_role_value 	= $_obj_field_source_host->get_id();																
																$sub_role_label		= $_obj_field_source_host->get_label();
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
														name	="sub_host_id[]" 
														id		="sub_host_id_<?php echo $_obj_data_sub_current->get_id(); ?>" 
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
                </div>
                <!--/Details-->
                 
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
                            +'<select name="sub_host_host[]" id="sub_host_host'+ $guid +'" class="form-control">'
                            +'<?php echo $host_list_new; ?>'
                            +'</select>'																		
                        +'</td>'					
                        +'<td colspan="2">'
                            +'<input type="hidden" name="sub_host_id[]" id="sub_host_id_js_'+$guid+'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
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