<?php 
	
	require(__DIR__.'/source/main.php');
	
	// Page caching.
	$page_obj = new \dc\cache\PageCache();
	
	// Inspection type this page refers to.
	$inspection_type = 1;
			
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
	$_main_data = new class_common_inspection_data();	
		
	// Ensure the main data ID member is same as navigation object ID.
	$_main_data->set_id($obj_navigation_rec->get_id());
			
	// Populate main data. 
	$query->set_sql('{call inspection_autoclave(@id = ?,
								@inspection_type = ?,							
								@sort_field 	= ?,
								@sort_order 	= ?,
								@nav_first		= ?,
								@nav_previous	= ?,
								@nav_next		= ?,
								@nav_last		= ?)}');	
	$nav_first 		= NULL;
	$nav_previous	= NULL;
	$nav_next		= NULL;
	$nav_last 		= NULL;
					
	$params = array(array($_main_data->get_id(), SQLSRV_PARAM_IN),
					array($inspection_type, 	SQLSRV_PARAM_IN),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_first,	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_previous, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_next, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));
					
	

	$query->set_param_array($params);
	$query->query_run();
	
	$query->get_line_config()->set_class_name('class_common_inspection_data');
	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();
	
	// Populate sub table data
	
	// --Parties
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('class_inspection_party_data');
	
	$_obj_data_sub_party_list = new SplDoublyLinkedList();
	if($query->get_row_exists()) $_obj_data_sub_party_list = $query->get_line_object_list();
	
	// --Visits
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('class_inspection_visit_data');
	
	$_obj_data_sub_visit_list = new SplDoublyLinkedList();
	if($query->get_row_exists()) $_obj_data_sub_visit_list = $query->get_line_object_list();

	// --Details
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('class_inspection_autoclave_detail_data');
	
	$_obj_data_sub_detail_list = new SplDoublyLinkedList();
	if($query->get_row_exists()) $_obj_data_sub_detail_list = $query->get_line_object_list();

			
	// Command action handling.	
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
			header('Location: inspection_list.php');
			break;
			
		case \dc\recordnav\COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call inspection_primary_delete(@id = ?)}');			
			
			$query->set_param_array(array(array($_main_data->get_id(), SQLSRV_PARAM_IN)));
			$query->query_run();
			
			// Refrsh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
			break;				
					
		case \dc\recordnav\COMMANDS::SAVE:
			
			// If this is set to false, data is not saved.
			$valid = TRUE;			
			
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
			
			// Not used for accounts.
			//$_main_data_label = $_main_data->get_label(); 
		
			// Call update stored procedure.
			$query->set_sql('{call inspection_primary_update(@id 			= ?,
													@log_update 	= ?, 
													@log_update_by	= ?, 
													@log_update_ip 	= ?,														 
													@label			= ?,
													@details		= ?,	
													@status			= ?,
													@room_code		= ?,
													@inspection_type = ?)}');
													
			$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
						array(date(APPLICATION_SETTINGS::TIME_FORMAT),	SQLSRV_PARAM_IN),
						array($access_obj->get_id(), 		SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
						array($_main_data->get_details(),		SQLSRV_PARAM_IN),						
						array($_main_data->get_status(), 		SQLSRV_PARAM_IN),
						array($_main_data->get_room_code(), 	SQLSRV_PARAM_IN),
						array($_main_data->get_inspection_type(), SQLSRV_PARAM_IN));
			
			//var_dump($params);
			
			// Did all data verify?
			if($valid === TRUE)
			{
			
				$query->set_param_array($params);			
				$query->query_run();
				
				// Repopulate main data object with results from merge query.
				$query->get_line_config()->set_class_name('class_common_inspection_data');
				$_main_data = $query->get_line_object();
				
				// Sub table saving.
				// --Parties
				$_obj_data_sub_request = new class_inspection_party_data();
				$_obj_data_sub_request->populate_from_request();
										
				// Call update stored procedure to process xml and update sub table.
				$query->set_sql('{call inspection_primary_party_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(APPLICATION_SETTINGS::TIME_FORMAT), 					SQLSRV_PARAM_IN),
								array($access_obj->get_id(),		SQLSRV_PARAM_IN),
								array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_param_array($params);			
				$query->query_run();
				
				// --visits
				$_obj_data_sub_request = new class_inspection_visit_data();
				$_obj_data_sub_request->populate_from_request();
										
				// Call update stored procedure to process xml and update sub table.
				$query->set_sql('{call inspection_primary_visit_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(APPLICATION_SETTINGS::TIME_FORMAT), 					SQLSRV_PARAM_IN),
								array($access_obj->get_id(),		SQLSRV_PARAM_IN),
								array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_param_array($params);			
				$query->query_run();
				
				// --detail
				$_obj_data_sub_request = new class_inspection_autoclave_detail_data();
				$_obj_data_sub_request->populate_from_request();
										
				// Call update stored procedure to process xml and update sub table.
				$query->set_sql('{call inspection_autoclave_detail_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(APPLICATION_SETTINGS::TIME_FORMAT), 					SQLSRV_PARAM_IN),
								array($access_obj->get_id(),		SQLSRV_PARAM_IN),
								array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_param_array($params);			
				$query->query_run();
							
				//echo '<br/> XML: '.$_obj_data_sub_request->xml();
				
				// Now that save operation has completed, reload page using ID from
				// database. This ensures the ID is always up to date, even with a new
				// or copied record.
				//header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id());					
			}
			
			break;			
	}
	
	
	
	$account_lookup = new \dc\access\lookup();
	$account_lookup->access_lookup($_main_data->get_account());
		
	// Populate main navigation with ID's from main data stored procedure.
	$obj_navigation_rec->set_id_first($nav_first);
	$obj_navigation_rec->set_id_previous($nav_previous);
	$obj_navigation_rec->set_id_next($nav_next);
	$obj_navigation_rec->set_id_last($nav_last);

	$obj_navigation_rec->generate_button_list();

	// List queries
		// --Status
		$_obj_data_list_status_list = new \data\Common();
	
		$query->set_sql('{call inspection_status_list_unpaged()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
		$_obj_data_list_status_list = array();
		if($query->get_row_exists() === TRUE) $_obj_data_list_status_list = $query->get_line_object_list();
		
		// --Accounts
		$_obj_field_source_account_list = new \data\Account();
	
		$query->set_sql('{call account_list_inspector()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Account');
		
		$_obj_field_source_account_list = array();
		if($query->get_row_exists() === TRUE) $_obj_field_source_account_list = $query->get_line_object_list();		
		
		// --Event type
		$_obj_data_list_event_type_list = new \data\Common();
	
		$query->set_sql('{call event_type_list_unpaged()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
		$_obj_data_list_event_type_list = array();
		if($query->get_row_exists() === TRUE) $_obj_data_list_event_type_list = $query->get_line_object_list();
		
		
		//-- Autoclave maker
		$_obj_data_list_autoclave_maker_list = new \data\Common();
	
		$query->set_sql('{call autoclave_maker_list_unpaged()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
		$_obj_data_list_autoclave_maker_list = array();
		if($query->get_row_exists() === TRUE) $_obj_data_list_autoclave_maker_list = $query->get_line_object_list();
		
		// --Biowaste status
		$_obj_data_list_biowaste_list = new \data\Common();
	
		$query->set_sql('{call biowaste_list_unpaged()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
		$_obj_data_list_biowaste_list = array();
		if($query->get_row_exists() === TRUE) $_obj_data_list_biowaste_list = $query->get_line_object_list();		
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Inspection Autoclave</title>        
        
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
                <h1>Inspection Autoclave<?php if($_main_data->get_label()) echo ' - '.$_main_data->get_label(); ?></h1>
                <p>This view allows you to view and modify the details of an individual inspection.</p>
            </div>
                        
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>                
                <?php require __DIR__.'/source/form_common_inspection.php'; ?>            
                
                <input type="hidden" name="inspection_type" id="inspection_type" value="<?php echo $inspection_type; ?>">
                                       
                <!--Details-->
                <div class="form-group">                    	
                    <div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                            <legend>Findings</legend>                                
                            <table class="table table-striped table-hover" id="tbl_sub_finding"> 
                                <thead>
                                    <tr>
                                        <th></th>                                                
                                        <th></th>                            
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="tbody_finding">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_detail_list) === TRUE)
                                    {        
                                        // Generate table row for each item in list.
                                        for($_obj_data_sub_detail_list->rewind(); $_obj_data_sub_detail_list->valid(); $_obj_data_sub_detail_list->next())
                                        {						
                                            $_obj_data_sub_detail = $_obj_data_sub_detail_list->current();
                                        
                                            // Blank IDs will cause a database error, so make sure there is a
                                            // usable one here.
                                            if(!$_obj_data_sub_detail->get_id()) $_obj_data_sub_party->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                            
                                        ?>
                                            <tr>
                                                <td>
                                                	<div class="form-group">
                                                        <label class="control-label col-sm-2" for="sub_autoclave_detail_maker_<?php echo $_obj_data_sub_detail->get_id(); ?>">Manufacturer:</label>
                                                        <!--Maker Value: <?php echo $_obj_data_sub_detail->get_maker(); ?>-->
                                                        
                                                        <div class="col-sm-10"> 
                                                            <select class		= "form-control"
                                                                    name		= "sub_autoclave_detail_maker[]" 
                                                                    id			= "sub_autoclave_detail_maker_<?php echo $_obj_data_sub_detail->get_id(); ?>">
                                                                    <option value="0">Select Manufacturer</option>
                                                                <?php
                                                                    if(is_object($_obj_data_list_autoclave_maker_list) === TRUE)
                                                                    {
                                                                        for($_obj_data_list_autoclave_maker_list->rewind(); $_obj_data_list_autoclave_maker_list->valid(); $_obj_data_list_autoclave_maker_list->next())
                                                                        {						
                                                                            $_obj_data_list_autoclave_maker = $_obj_data_list_autoclave_maker_list->current();
                                                                            
                                                                            $selected = NULL;
                                                                            
                                                                            if($_obj_data_list_autoclave_maker->get_id() == $_obj_data_sub_detail->get_maker())
                                                                            {
                                                                                $selected = ' selected ';
                                                                            }
                                                                            ?>
                                                                                <option value="<?php echo $_obj_data_list_autoclave_maker->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_data_list_autoclave_maker->get_label(); ?></option>
                                                                            <?php										
                                                                        }
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                
                                                	<div class="form-group">
                                                        <label class="control-label col-sm-2" for="sub_autoclave_detail_model_<?php echo $_obj_data_sub_detail->get_id(); ?>" list="autoclave_models">Model:</label>
                                                        <div class="col-sm-10">
                                                            <input 
                                                                type		= "text" 
                                                                class		= "form-control"  
                                                                name		= "sub_autoclave_detail_model[]" 
                                                                id			= "sub_autoclave_detail_model_<?php echo $_obj_data_sub_detail->get_id(); ?>" 
                                                                placeholder	= "Model of unit." 
                                                                value		= "<?php echo $_obj_data_sub_detail->get_model(); ?>">
                                                        </div>
                                                    </div>
                        
                                                    <div class="form-group">
                                                        <label class="control-label col-sm-2" for="sub_autoclave_detail_serial_<?php echo $_obj_data_sub_detail->get_id(); ?>">Serial #:</label>
                                                        <div class="col-sm-10">
                                                            <input 
                                                                type		= "text" 
                                                                class		= "form-control"  
                                                                name		= "sub_autoclave_detail_serial[]" 
                                                                id			= "sub_autoclave_detail_serial_<?php echo $_obj_data_sub_detail->get_id(); ?>" 
                                                                placeholder	= "Serial # of unit." 
                                                                value		= "<?php echo $_obj_data_sub_detail->get_serial(); ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">    
                                                        <label class="control-label col-sm-2" for="sub_autoclave_detail_tag_<?php echo $_obj_data_sub_detail->get_id(); ?>">Property #:</label>
                                                        <div class="col-sm-10">
                                                            <input 
                                                                type		= "text" 
                                                                class		= "form-control"  
                                                                name		= "sub_autoclave_detail_tag[]" 
                                                                id			= "sub_autoclave_detail_tag_<?php echo $_obj_data_sub_detail->get_id(); ?>" 
                                                                placeholder	= "UK Property tag number." 
                                                                value		= "<?php echo $_obj_data_sub_detail->get_tag(); ?>">
                                                        </div>
                                                    </div>
                                                
                                                    <div class="form-group">
                                                        <label class="control-label col-sm-2" for="sub_autoclave_detail_biowaste_<?php echo $_obj_data_sub_detail->get_id(); ?>">Biowaste:</label>
                                                        <div class="col-sm-10">         
                                                            <!--biowaste: <?php echo $_obj_data_sub_detail->get_biowaste(); ?>-->
                                                            
                                                                <?php
																if(is_object($_obj_data_list_biowaste_list) === TRUE)
																{
																	for($_obj_data_list_biowaste_list->rewind(); $_obj_data_list_biowaste_list->valid(); $_obj_data_list_biowaste_list->next())
																	{						
																		$_obj_data_list_biowaste = $_obj_data_list_biowaste_list->current();
																		
																		$selected = NULL;
																		
																		if($_obj_data_list_biowaste->get_id() == $_obj_data_sub_detail->get_biowaste())
																		{
																			$selected = ' checked ';
																		}
																		?>
																			<label class="radio-inline">
																				<input 
																					type	="radio" 
																					name	="sub_autoclave_detail_biowaste" 
																					id		="sub_autoclave_detail_biowaste_<?php echo $_obj_data_list_biowaste->get_id(); ?>"
																					value	="<?php echo $_obj_data_list_biowaste->get_id(); ?>" <?php echo $selected;?> 
																					><?php echo $_obj_data_list_biowaste->get_label(); ?>
																			</label>                                                    
																		<?php										
																	}
																}
																?>
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                                                                            
                                                    
                                                    <div class="form-group collapse" id="div_sub_autoclave_detail_details_<?php echo $_obj_data_sub_detail->get_id(); ?>">
                                                        <label class="control-label col-sm-2" for="div_sub_autoclave_detail_details_<?php echo $_obj_data_sub_detail->get_id(); ?>">Comments:</label>
                                                        <div class="col-sm-10">
                                                            <textarea 
                                                                class="form-control" 
                                                                rows="5" 
                                                                name="sub_autoclave_detail_details[]" 
                                                                id="sub_autoclave_detail_details_<?php echo $_obj_data_sub_detail->get_id(); ?>"><?php echo $_obj_data_sub_detail->get_details(); ?></textarea>
                                                        </div>
                                                    </div>
                                                   
                                                    
                                                </td>
                                                                                       
                                                <td>													
                                                    <input 
                                                        type	="hidden" 
                                                        name	="sub_autoclave_detail_id[]" 
                                                        id		="sub_autoclave_detail_id_<?php echo $_obj_data_sub_detail->get_id(); ?>" 
                                                        value	="<?php echo $_obj_data_sub_detail->get_id(); ?>" />
                                                </td>       
                                                <td>
                                                    <?php
                                                        $btn_type 	= 'btn-primary';
                                                        $btn_title	= 'Show or hide comments.';
                                                        
                                                        if($_obj_data_sub_detail->get_details())
                                                        {
                                                            $btn_type = 'btn-warning';	
                                                            $btn_title	= 'This row has comments. Click to show or hide.';	
                                                        }
                                                                                                                    
                                                    ?>
                                                    
                                                    <a  class		= "btn <?php echo $btn_type; ?>  btn-sm"                                                           
                                                        type		= "button"
                                                        data-toggle	= "collapse" 
                                                        title		= "<?php echo $btn_title; ?>"
                                                        data-target	= "#div_sub_autoclave_detail_details_<?php echo $_obj_data_sub_detail->get_id(); ?>"><span class="glyphicon glyphicon-pencil"></span></a>
                                                    
                                                </td>
                                                <td>   
                                                    <button 
                                                        type	="button" 
                                                        class 	="btn btn-danger btn-sm" 
                                                        name	="sub_autoclave_detail_row_del" 
                                                        id		="sub_autoclave_detail_row_del_<?php echo $_obj_data_sub_detail->get_id(); ?>" 
                                                        title	="Remove this item."
                                                        onclick="deleteRow_sub_finding(this)"><span class="glyphicon glyphicon-minus"></span></button> 
                                                            
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
                                id		="row_add_detail"
                                title	="Add new item."
                                onclick	="insRow_finding()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                    </div>                        
                </div>
                <!--/Details-->
                
                <!--Save button-->
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
        
            var $temp_finding = 0;
            
            function deleteRow_sub_finding(row)
            {
                var i=row.parentNode.parentNode.rowIndex;
                document.getElementById('tbl_sub_finding').deleteRow(i);
            }
            
            function insRow_finding()
            {  
                $('.tbody_finding').append(
					'<tr>'
						+'<td>'
							+'<div class="form-group">'
								+'<label class="control-label col-sm-2" for="sub_autoclave_detail_maker_'+$temp_finding+'">Manufacturer:</label>'
																
								+'<div class="col-sm-10">' 
									+'<select class		= "form-control" '
											+'name		= "sub_autoclave_detail_maker[]" '
											+'id		= "sub_autoclave_detail_maker_'+$temp_finding+'">'
											+'<option value="0">Select Manufacturer</option>'
										<?php
											if(is_object($_obj_data_list_autoclave_maker_list) === TRUE)
											{
												for($_obj_data_list_autoclave_maker_list->rewind(); $_obj_data_list_autoclave_maker_list->valid(); $_obj_data_list_autoclave_maker_list->next())
												{						
													$_obj_data_list_autoclave_maker = $_obj_data_list_autoclave_maker_list->current();
													
													?>
														+'<option value="<?php echo $_obj_data_list_autoclave_maker->get_id(); ?>"><?php echo $_obj_data_list_autoclave_maker->get_label(); ?></option>'
													<?php										
												}
											}
										?>
									+'</select>'
								+'</div>'
							+'</div>'
						
							+'<div class="form-group">'
								+'<label class="control-label col-sm-2" for="sub_autoclave_detail_model_'+$temp_finding+'" list="autoclave_models">Model:</label>'
								+'<div class="col-sm-10">'
									+'<input ' 
										+'type		= "text" ' 
										+'class		= "form-control" '  
										+'name		= "sub_autoclave_detail_model[]" ' 
										+'id			= "sub_autoclave_detail_model_'+$temp_finding+'" ' 
										+'placeholder	= "Model of unit." ' 
										+'value		= "">'
								+'</div>'
							+'</div>'	
							
							+'<div class="form-group">'
								+'<label class="control-label col-sm-2" for="sub_autoclave_detail_serial_'+$temp_finding+'">Serial #:</label>'
								+'<div class="col-sm-10">'
									+'<input ' 
										+'type		= "text" ' 
										+'class		= "form-control" '  
										+'name		= "sub_autoclave_detail_serial[]" ' 
										+'id			= "sub_autoclave_detail_serial_'+$temp_finding+'" ' 
										+'placeholder	= "Serial # of unit." ' 
										+'value		= "">'
								+'</div>'
							+'</div>'
							
							+'<div class="form-group">'    
								+'<label class="control-label col-sm-2" for="sub_autoclave_detail_tag_'+$temp_finding+'">Property #:</label>'
								+'<div class="col-sm-10">'
									+'<input ' 
										+'type		= "text" ' 
										+'class		= "form-control" '  
										+'name		= "sub_autoclave_detail_tag[]" ' 
										+'id			= "sub_autoclave_detail_tag_'+$temp_finding+'" ' 
										+'placeholder	= "UK Property tag number." '
										+'value		= "">'
								+'</div>'
							+'</div>'				

							+'<div class="form-group">'
								+'<label class="control-label col-sm-2" for="sub_autoclave_detail_biowaste_'+$temp_finding+'">Biowaste:</label>'
								+'<div class="col-sm-10">'         
																		
										<?php
										if(is_object($_obj_data_list_biowaste_list) === TRUE)
										{
											for($_obj_data_list_biowaste_list->rewind(); $_obj_data_list_biowaste_list->valid(); $_obj_data_list_biowaste_list->next())
											{						
												$_obj_data_list_biowaste = $_obj_data_list_biowaste_list->current();
																								
												?>
													+'<label class="radio-inline">'
														+'<input '
															+'type	="radio" '
															+'name	="sub_autoclave_detail_biowaste" '
															+'id	="sub_autoclave_detail_biowaste_'+$temp_finding+'" '
															+'value	="<?php echo $_obj_data_list_biowaste->get_id(); ?>" '
															+'><?php echo $_obj_data_list_biowaste->get_label(); ?>'
													+'</label>'                                                    
												<?php										
											}
										}
										?>									
								+'</div>'
							+'</div>'
                           
                            +'<div class="form-group collapse" id="div_sub_autoclave_detail_details_'+$temp_finding+'">'
                                +'<label class="control-label col-sm-2" for="sub_autoclave_detail_details_'+$temp_finding+'">Comments:</label>'
                                +'<div class="col-sm-10">'
                                    +'<textarea ' 
                                        +'class	= "form-control" ' 
                                        +'rows 	= "5" ' 
                                        +'name	= "sub_autoclave_detail_details[]" ' 
                                        +'id	= "sub_autoclave_detail_details_'+$temp_finding+'"></textarea>'
                                +'</div>'
                            +'</div>'
                            
                        +'</td>'
                                                               
                        +'<td>'													
                            +'<input ' 
                                +'type	= "hidden" ' 
                                +'name	= "sub_autoclave_detail_id[]" ' 
                                +'id	= "sub_autoclave_detail_id_'+$temp_finding+'" ' 
                                +'value	= "<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" /> '
                        +'</td>'
                        +'<td>'
                            +'<a class		= "btn btn-primary btn-sm" '                                                           
                            +'type			= "button" '
                            +'data-toggle	= "collapse" ' 
                            +'title			= "Show or hide comments." '
                            +'data-target	= "#div_sub_autoclave_detail_details_'+$temp_finding+'"><span class="glyphicon glyphicon-pencil"></span></a>'
                        +'</td>'
                        +'<td>'
                            +'<button ' 
                                +'type	= "button" ' 
                                +'class = "btn btn-danger btn-sm" ' 
                                +'name	= "sub_autoclave_detail_row_del" ' 
                                +'id	= "sub_autoclave_detail_row_del_'+$temp_finding+'" ' 
                                +'onclick = "deleteRow_sub_finding(this)"><span class="glyphicon glyphicon-minus"></span></button>'       
                        +'</td>'
                    +'</tr>'			
                );
                
                tinymce.init({
                selector: '#sub_autoclave_detail_details_'+$temp_finding,
                plugins: [
                    "advlist autolink lists link image charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media table contextmenu paste"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
        
                
                $temp_finding--;
            }
            
            // Update correction list items based on category selection.
            function update_corrections($val)
            {
                var $update_select_id = ".update_source_" + $($val).attr('id');			
                
                // Get element by css seelctor - this returns a list.
                var $target = document.querySelectorAll($update_select_id);
        
                // Iterate list and update al elements (In most cases, there will
                // only be one).
                for (var i = 0; i < $($target).length; i++) 
                {
                    $($target).attr('disabled', false);
                    $($target).load("<?php echo APPLICATION_SETTINGS::DIRECTORY_PRIME; ?>/inspection_saa_corrections_list.php?category=" + $($val).val());
                }			
            }
                     
        </script>
	</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>