<?php 
	
	require(__DIR__.'/source/main.php');
	
	// Page caching.
	$page_obj = new \dc\cache\PageCache();
			
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	$obj_navigation_main->generate_markup_nav();
	$obj_navigation_main->generate_markup_footer();	
				
	// Set up database.
	$db_conn_set = new ConnectConfig();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new Connect($db_conn_set);
	$query = new \dc\yukon\Database($db);		
			
	// Record navigation.
	$obj_navigation_rec = new \dc\recordnav\RecordNav();	
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
	$url_query->set_data('action', $obj_navigation_rec->get_action());
	$url_query->set_data('id', $obj_navigation_rec->get_id());
		
	// User access.
	$access_obj = new \dc\stoeckl\status();
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
			
	switch($obj_navigation_rec->get_action())
	{		
	
		default:		
		case \dc\recordnav\COMMANDS::NEW_BLANK:
		
			break;
			
		case \dc\recordnav\COMMANDS::NEW_COPY:			
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();			
			break;
			
		case \dc\recordnav\COMMANDS::LISTING:
			
			// Direct to listing.				
			header('Location: '.basename(__FILE__, '.php').'_list.php');
			break;
			
		case \dc\recordnav\COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call audit_question_inclusion_delete(@id = ?,													 
									@update_by	= ?, 
									@update_ip 	= ?)}');
			
			$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
						array($access_obj->get_id(), 		SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
			
						
			$query->set_param_array($params);
			$query->query_run();
			
			// Refrsh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
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
			$_obj_data_sub_request = new class_account_role_data();
			$_obj_data_sub_request->populate_from_request();
		
			// Let's get account info fromt he active directory system. We'll need to put
			// names int our own database so we can control ordering of output.
			$account_lookup = new \dc\stoeckl\lookup();
			$account_lookup->access_lookup($_main_data->get_account());
		
			// Call update stored procedure.
			$query->set_sql('{call audit_question_inclusion_update(@id 				= ?,														 
													@label 			= ?,
													@details 		= ?,													 
													@log_update_by	= ?, 
													@log_update_ip 	= ?)}');
													
			$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
						array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
						array($_main_data->get_details(),		SQLSRV_PARAM_IN),						
						array($access_obj->get_id(), 			SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
			
			$query->set_param_array($params);			
			$query->query_run();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_config()->set_class_name('\data\Common');
			$_main_data = $query->get_line_object();
			
			// Now that save operation has completed, reload page using ID from
			// database. This ensures the ID is always up to date, even with a new
			// or copied record.
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id());
			
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
	$query->set_sql('{call audit_question_inclusion(@id = ?)}');
	$params = array(array($_main_data->get_id(), SQLSRV_PARAM_IN));

	$query->set_param_array($params);
	$query->query_run();
	
	// Query for navigation data and populate navigation object.
	$_obj_navigation = new \dc\recordnav\DataNavigation();
	
	$query->get_line_config()->set_class_name('\dc\recordnav\DataNavigation');	
	if($query->get_row_exists() === TRUE) $_obj_navigation = $query->get_line_object();
		
	$obj_navigation_rec->set_id_first($_obj_navigation->get_id_first());
	$obj_navigation_rec->set_id_previous($_obj_navigation->get_id_previous());
	$obj_navigation_rec->set_id_next($_obj_navigation->get_id_next());
	$obj_navigation_rec->set_id_last($_obj_navigation->get_id_last());
	
	// Query for primary data.
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	$obj_navigation_rec->generate_button_list();
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Inspection Type Detail</title>        
        
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
                <h1>Inspection Type Detail<?php if($_main_data->get_label()) echo ' - '.$_main_data->get_label(); ?></h1>
                <p class="lead">This screen allows for adding and editing inspection types.</p>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>  
                
             	<div class="form-group">       
                    <label class="control-label col-sm-2" for="last_update">Last Update:</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"> <a href = "common_log_list.php?id=<?php echo $_main_data->get_id();  ?>"
                                                            data-toggle	= ""
                                                            title		= "View log for this record."
                                                             target		= "_new" 
                            ><?php if(is_object($_main_data->get_log_update())) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_main_data->get_log_update()->getTimestamp()); ?></a></p>
                    </div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="label">Label:</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="label" id="label" placeholder="Item label" value="<?php echo $_main_data->get_label(); ?>" required>
                	</div>
                </div>               
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="details">Details:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control" rows="5" name="details" id="details"><?php echo $_main_data->get_details(); ?></textarea>
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
</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>