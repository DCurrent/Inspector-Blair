<?php 
	
	require(__DIR__.'/source/main.php');
	require(__DIR__.'/source/common_functions/common_list.php');
	
	$_page_config_config = new \dc\application\CommonEntry($yukon_connection);	
	$_page_config = new \dc\application\CommonEntryConfig($_page_config_config);	
	$_layout = $_page_config->create_config_object();
	
	// Delete this record.
	function action_delete($_main_data, $access_obj, $query)
	{
		// Populate the object from post values.			
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
		
		// Refrsh page to the previous record.				
		header('Location: '.$_SERVER['PHP_SELF']);
	}
	
	
	
	// Page caching.
	$page_obj = new \dc\cache\PageCache();
			
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	$obj_navigation_main->generate_markup_nav();
	$obj_navigation_main->generate_markup_footer();	
				
	// Set up database.
	$query = new \dc\yukon\Database($yukon_connection);		
			
	// Record navigation.
	$obj_navigation_rec = new \dc\recordnav\RecordNav();	
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
	$url_query->set_data('action', $obj_navigation_rec->get_action());
	$url_query->set_data('id', $obj_navigation_rec->get_id());
	$url_query->set_data('id_key', $obj_navigation_rec->get_id_key());
	$url_query->set_data('id_form', $_layout->get_id());
		
	// User access.
	$access_obj = new \dc\access\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
	$access_obj->get_config()->set_database($yukon_database);
	$access_obj->set_redirect($url_query->return_url());
	
	$access_obj->verify();	
	$access_obj->action();
	
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
	
	// Initialize our data objects. This is just in case there is no table
	// data for any of the navigation queries to find, we are making new
	// records, or copies of records. It also has the side effect of enabling 
	// IDE type hinting.
	$_main_data = new \data\Account();	
		
	// Ensure the main data ID member is same as navigation object ID.
	$_main_data->set_id($obj_navigation_rec->get_id());
	$_main_data->set_id_key($obj_navigation_rec->get_id_key());
	
	switch($obj_navigation_rec->get_action())
	{		
		default:		
		case \dc\recordnav\COMMANDS::NEW_BLANK:
			break;
			
		case \dc\recordnav\COMMANDS::LISTING:
		
			common_list($_layout);
			
			break;
			
		case \dc\recordnav\COMMANDS::DELETE:						
			
			action_delete($_main_data, $access_obj, $query);	
			break;				
					
		case \dc\recordnav\COMMANDS::SAVE:
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != \dc\recordnav\COMMANDS::SAVE) break;
									
			// Save the record. Saving main record is straight forward. We’ll run the populate method on our 
			// main data object which will gather up post values. Then we can run a query to merge the values into 
			// database table. We’ll then get the id from saved record (since we are using a surrogate key, the ID
			// should remain static unless this is a brand new record). 
			
			// If necessary we will then save any sub records (see each for details).
			
			// Finally, we redirect to the current page using the freshly acquired id. That will ensure we have 
			// always an up to date ID for our forms and navigation system.			
		
			// Populate the object from post values.			
			$_main_data->populate_from_request();
			
			// --Sub data: Role.
			$_obj_data_sub_request = new \data\AccountRole();
			$_obj_data_sub_request->populate_from_request();
		
			// Let's get account info fromt he active directory system. We'll need to put
			// names int our own database so we can control ordering of output.
			$account_lookup = new \dc\access\lookup();
			$account_lookup->lookup($_main_data->get_account());
		
			// Call update stored procedure.
			$query->set_sql('{call account_update(@id			= ?,
													@log_update_by	= ?, 
													@log_update_ip 	= ?,										 
													@account 		= ?,
													@department 	= ?,
													@details		= ?,
													@name_f			= ?,
													@name_l			= ?,
													@name_m			= ?,
													@sub_role_xml	= ?)}');
													
			$params = array(array('<root><row id="'.$_main_data->get_id().'"/></root>', 		SQLSRV_PARAM_IN),
						array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_account(), 		SQLSRV_PARAM_IN),						
						array($_main_data->get_department(),	SQLSRV_PARAM_IN),						
						array($_main_data->get_details(), 		SQLSRV_PARAM_IN),
						array($account_lookup->get_DataAccount()->get_name_f(), SQLSRV_PARAM_IN),
						array($account_lookup->get_DataAccount()->get_name_l(), SQLSRV_PARAM_IN),
						array($account_lookup->get_DataAccount()->get_name_m(), SQLSRV_PARAM_IN),
						array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN));
			
			//var_dump($params);
			//exit;
			
			$query->set_params($params);			
			$query->query_run();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_config()->set_class_name('\data\Account');
			$_main_data = $query->get_line_object();
			
			// Now that save operation has completed, reload page using ID from
			// database. This ensures the ID is always up to date, even with a new
			// or copied record.
			// Initialize redirect url object and 
			// populate variables.			
			$url_query->set_data('id', $_main_data->get_id());
			$url_query->set_url_base($_SERVER['PHP_SELF']);
			
			header('Location: '.$url_query->return_url());
			
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
	
	//////
	
	$query->set_sql('{call account(@param_filter_id = ?,
									@param_filter_id_key = ?)}');
	$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($_main_data->get_id_key(), 	SQLSRV_PARAM_IN));

	$query->set_params($params);
	$query->query_run();
	
	//Query for navigation data and populate navigation object.	
		$_obj_navigation = new \dc\recordnav\DataNavigation();
		
		$query->get_line_config()->set_class_name('\dc\recordnav\DataNavigation');	
		if($query->get_row_exists() === TRUE) $_obj_navigation = $query->get_line_object();
			
		$obj_navigation_rec->set_id_first($_obj_navigation->get_id_first());
		$obj_navigation_rec->set_id_previous($_obj_navigation->get_id_previous());
		$obj_navigation_rec->set_id_next($_obj_navigation->get_id_next());
		$obj_navigation_rec->set_id_last($_obj_navigation->get_id_last());
		
		$obj_navigation_rec->generate_button_list();
	
	// Query for primary data.	
		$query->get_next_result();
		
		$query->get_line_config()->set_class_name('\data\Account');	
		if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	// Sub table (role) generation
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_data_sub_arr = array();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_arr = $query->get_line_object_list();
		
	

	

	// List queries
		// Roles
		$_obj_field_source_role_list = new \data\Common();
	
		$query->set_sql('{call account_role_list(@page_current = ?)}');
		$params = array(array(-1, SQLSRV_PARAM_IN));
		
		$query->set_params($params);
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
		$_obj_field_source_role_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE) $_obj_field_source_role_list = $query->get_line_object_list();
		
		// Generate a list for new record insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$role_list_options = NULL;
				
		for($_obj_field_source_role_list->rewind();	$_obj_field_source_role_list->valid(); $_obj_field_source_role_list->next())
		{	                                                               
			$_obj_field_source_role = $_obj_field_source_role_list->current();					
			
			$role_list_options .= '<option value="'.$_obj_field_source_role->get_id().'">'.$_obj_field_source_role->get_label().'</option>';					
		}
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Account Detail</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="http://ehs.uky.edu/libraries/vendor/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
        tinymce.init({
            selector: "textarea",
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
        </script>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">           
                <h1>Account Entry<?php if($_main_data->get_name_l()) echo ' - '.$_main_data->get_name_l().', '.$_main_data->get_name_f(); ?></h1>
                <p>This screen allows for adding and editing individual accounts.</p>
                <?php
					// If this isn't the active version, better alert user.
					if(!$_main_data->get_active() && $_main_data->get_id_key())
					{
					?>
                    	<div class="alert alert-warning">
                        	<strong>Notice:</strong> You are currently viewing an inactive revision of this record. Saving will make this the active revision. To return to the currently active revision without saving, click <a href="<?php echo $_SERVER['PHP_SELF'].'?id='.$_main_data->get_id(); ?>">here</a>.
                        </div>
                    <?php
					}
				?>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>  
                
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
                	<label class="control-label col-sm-2" for="account">Account</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="account" id="account" placeholder="Link Blue Account" value="<?php echo $_main_data->get_account(); ?>" required>
                	</div>
                </div>               
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="name_f">Name (First)</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="name_f" id="name_f" placeholder="First Name" value="<?php echo $_main_data->get_name_f(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="name_m">Name (Middle)</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="name_m" id="name_m" placeholder="Middle Name" value="<?php echo $_main_data->get_name_m(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="name_m">Name (Last)</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="name_l" id="name_l" placeholder="Last Name" value="<?php echo $_main_data->get_name_l(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="details">Details</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control" rows="5" name="details" id="details"><?php echo $_main_data->get_details(); ?></textarea>
                	</div>
                </div> 
                
                <div class="form-group">                    	
                        <div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>Roles</legend>                    
                            <p>Roles assigned to this account.</p>
      						
                            <table class="table table-striped table-hover" id="POITable"> 
                                <thead>
                                    <tr>
                                        <th colspan="2">Role</th> 
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_arr) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_arr->rewind(); $_obj_data_sub_arr->valid(); $_obj_data_sub_arr->next())
										{						
											$_obj_data_sub = $_obj_data_sub_arr->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_role_role[]"
                                                        id		= "sub_role_role_<?php echo $_obj_data_sub->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
														echo '<!--get_item(): '.$_obj_data_sub->get_item().'-->';
                                                        if(is_object($_obj_field_source_role_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_role_list->rewind();	$_obj_field_source_role_list->valid(); $_obj_field_source_role_list->next())
                                                            {	                                                               
																$_obj_field_source_role = $_obj_field_source_role_list->current();
																
																$sub_role_value 	= $_obj_field_source_role->get_id();																
																$sub_role_label		= $_obj_field_source_role->get_label();
																$sub_role_selected 	= NULL;
																
																if($_obj_data_sub->get_item() === $sub_role_value) $sub_role_selected = ' selected '
																
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
														name	="sub_role_id[]" 
														id		="sub_role_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
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
                    
                                        
                <hr />
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>               
            </form>
            
            <?php echo $obj_navigation_main->get_markup_footer(); ?>
        </div><!--container-->        
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40196994-1', 'uky.edu');
  ga('send', 'pageview');
  
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

	<!-- Latest compiled JavaScript -->
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
						+'<select name="sub_role_role[]" id="sub_role_role_'+ $guid +'" class="form-control">'
						+'<?php echo $role_list_options; ?>'
						+'</select>'																		
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_role_id[]" id="sub_role_id_js_'+$guid+'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+$guid+'" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
			
			tinymce.init({
            selector: '#sub_details_'+ $guid,
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
		}			
		 
	</script>
</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>