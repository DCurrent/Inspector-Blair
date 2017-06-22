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
	$_main_data = new class_audit_question_data();	
		
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
			header('Location: audit_question_list.php');
			break;
			
		case \dc\recordnav\COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call audit_question_delete(@id = ?,													 
									@update_by	= ?, 
									@update_ip 	= ?)}');
			
			$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
						array($access_obj->get_id(), 			SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
			
						
			$query->set_params($params);
			$query->query();
			
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
			
			// --Sub data: Category.
			$_obj_data_sub_category_request = new class_audit_question_category_data();
			$_obj_data_sub_category_request->populate_from_request();
			
			// --Sub data: Inclusion.
			$_obj_data_sub_inclusion_request = new class_audit_question_inclusion_data();
			$_obj_data_sub_inclusion_request->populate_from_request();
			
			// --Sub data: Rating.
			$_obj_data_sub_rating_request = new class_audit_question_rating_data();
			$_obj_data_sub_rating_request->populate_from_request();
			
			// --Sub data: Reference.
			$_obj_data_sub_reference_request = new class_audit_question_reference_data();
			$_obj_data_sub_reference_request->populate_from_request();
		
			// Call update stored procedure.
			$query->set_sql('{call audit_question_update(@id 					= ?,														 
													@label 				= ?,
													@details 			= ?,
													@finding			= ?,
													@corrective_action	= ?,
													@sub_category_xml	= ?,
													@sub_inclusion_xml	= ?,
													@sub_rating_xml		= ?,
													@sub_reference_xml	= ?,													 
													@log_update_by		= ?, 
													@log_update_ip 		= ?)}');
													
			$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
						array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
						array($_main_data->get_details(),		SQLSRV_PARAM_IN),						
						array($_main_data->get_finding(), 		SQLSRV_PARAM_IN),
						array($_main_data->get_corrective_action(), SQLSRV_PARAM_IN),
						array($_obj_data_sub_category_request->xml(), 	SQLSRV_PARAM_IN),
						array($_obj_data_sub_inclusion_request->xml(), 	SQLSRV_PARAM_IN),
						array($_obj_data_sub_rating_request->xml(), 	SQLSRV_PARAM_IN),
						array($_obj_data_sub_reference_request->xml(), 	SQLSRV_PARAM_IN),
						array($access_obj->get_id(), 			SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
			
			
			
			$query->set_params($params);			
			$query->query();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_params()->set_class_name('class_audit_question_data');
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
	$query->set_sql('{call audit_question(@id = ?)}');
	$params = array(array($_main_data->get_id(), SQLSRV_PARAM_IN));

	$query->set_params($params);
	$query->query();
	
	// Query for navigation data and populate navigation object.
	$_obj_navigation = new \dc\recordnav\DataNavigation();
	
	$query->get_line_params()->set_class_name('\dc\recordnav\DataNavigation');	
	if($query->get_row_exists() === TRUE) $_obj_navigation = $query->get_line_object();
		
	$obj_navigation_rec->set_id_first($_obj_navigation->get_id_first());
	$obj_navigation_rec->set_id_previous($_obj_navigation->get_id_previous());
	$obj_navigation_rec->set_id_next($_obj_navigation->get_id_next());
	$obj_navigation_rec->set_id_last($_obj_navigation->get_id_last());
	
	// Query for primary data.
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_audit_question_data');	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	// Sub table (category) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\data\Common');
	
	$_obj_data_sub_category_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_category_list = $query->get_line_object_list();
	
	// Sub table (inclusion) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\data\Common');
	
	$_obj_data_sub_inclusion_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_inclusion_list = $query->get_line_object_list();
	
	// Sub table (rating) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\data\Common');
	
	$_obj_data_sub_rating_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_rating_list = $query->get_line_object_list();
		
	// Sub table (reference) generation.
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\data\Common');
	
	$_obj_data_sub_reference_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_reference_list = $query->get_line_object_list();
	

	$obj_navigation_rec->generate_button_list();

	// List queries
		// Categories
		$query->set_sql('{call audit_question_category_list(@page_current = ?)}');											
		
		$params = array(array(-1,			SQLSRV_PARAM_IN));

		$query->set_params($params);
		$query->query();
		
		$query->get_line_params()->set_class_name('\data\Common');
		
		$_obj_field_source_category_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE) $_obj_field_source_category_list = $query->get_line_object_list();
		
		// Generate a list for new record insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$category_list_options = NULL;
				
		for($_obj_field_source_category_list->rewind();	$_obj_field_source_category_list->valid(); $_obj_field_source_category_list->next())
		{	                                                               
			$_obj_field_source_category = $_obj_field_source_category_list->current();					
			
			$category_list_options .= '<option value="'.$_obj_field_source_category->get_id().'">'.$_obj_field_source_category->get_label().'</option>';					
		}
		
		// Inclusions
		$query->set_sql('{call audit_question_inclusion_list(@paged				= ?,
										@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?)}');											
		$page_last 	= NULL;
		$row_count 	= NULL;		
		
		$params = array(array(NULL,			SQLSRV_PARAM_IN),
						array(NULL, 		SQLSRV_PARAM_IN), 
						array(NULL, 		SQLSRV_PARAM_IN), 
						array($page_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
						array($row_count, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

		$query->set_params($params);
		$query->query();
		
		$query->get_line_params()->set_class_name('\data\Common');
		
		$_obj_field_source_inclusion_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE) $_obj_field_source_inclusion_list = $query->get_line_object_list();
		
		// Generate a list for new record insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$inclusion_list_options = NULL;
				
		for($_obj_field_source_inclusion_list->rewind();	$_obj_field_source_inclusion_list->valid(); $_obj_field_source_inclusion_list->next())
		{	                                                               
			$_obj_field_source_inclusion = $_obj_field_source_inclusion_list->current();					
			
			$inclusion_list_options .= '<option value="'.$_obj_field_source_inclusion->get_id().'">'.$_obj_field_source_inclusion->get_label().'</option>';					
		}
		
		// Ratings
		$query->set_sql('{call audit_question_rating_list(@paged				= ?,
										@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?)}');											
		$page_last 	= NULL;
		$row_count 	= NULL;		
		
		$params = array(array(NULL,			SQLSRV_PARAM_IN),
						array(NULL, 		SQLSRV_PARAM_IN), 
						array(NULL, 		SQLSRV_PARAM_IN), 
						array($page_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
						array($row_count, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

		$query->set_params($params);
		$query->query();
		
		$query->get_line_params()->set_class_name('\data\Common');
		
		$_obj_field_source_rating_list = new SplDoublyLinkedList();
		if($query->get_row_exists() === TRUE) $_obj_field_source_rating_list = $query->get_line_object_list();
		
		// Generate a list for new record insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$rating_list_options = NULL;
				
		for($_obj_field_source_rating_list->rewind();	$_obj_field_source_rating_list->valid(); $_obj_field_source_rating_list->next())
		{	                                                               
			$_obj_field_source_rating = $_obj_field_source_rating_list->current();					
			
			$rating_list_options .= '<option value="'.$_obj_field_source_rating->get_id().'">'.$_obj_field_source_rating->get_label().'</option>';					
		}
		
		
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Audit Question Detail</title>        
        
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
                <h1>Audit Question Entry<?php if($_main_data->get_label()) echo ' - '.$_main_data->get_label(); ?></h1>
                <p>This screen allows for adding and editing individual accounts.</p>
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
                
                <!--<div class="form-group">
                	<label class="control-label col-sm-2" for="label">Label:</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="label" id="label" placeholder="Question label" value="<?php echo $_main_data->get_label(); ?>">
                	</div>
                </div>               
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="details">Details:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control" rows="5" name="details" id="details"><?php echo $_main_data->get_details(); ?></textarea>
                	</div>
                </div>--> 
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="finding">Finding:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control" rows="5" name="finding" id="finding"><?php echo $_main_data->get_finding(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="corrective_action">Corrective Action:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control" rows="5" name="corrective_action" id="corrective_action"><?php echo $_main_data->get_corrective_action(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="audit_question_category_list.php" target="_new">Categories</a></legend>                    
                            <p>Categories assigned to this question.</p>
      						
                            <table class="table table-striped table-hover" id="table_sub_categories"> 
                                <thead>
                                    
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec_category">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_category_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_category_list->rewind(); $_obj_data_sub_category_list->valid(); $_obj_data_sub_category_list->next())
										{						
											$_obj_data_sub = $_obj_data_sub_category_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_category_item_id[]"
                                                        id		= "sub_category_item_id_<?php echo $_obj_data_sub->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
                                                        if(is_object($_obj_field_source_category_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_category_list->rewind();	$_obj_field_source_category_list->valid(); $_obj_field_source_category_list->next())
                                                            {	                                                               
																$_obj_field_source_category = $_obj_field_source_category_list->current();
																
																$sub_value 		= $_obj_field_source_category->get_id();																
																$sub_label		= $_obj_field_source_category->get_label();
																$sub_selected 	= NULL;
																
																if($_obj_data_sub->get_item() === $sub_value) $sub_selected = ' selected '
																
																?>
                                                                <option value="<?php echo $sub_value; ?>"<?php echo $sub_selected ?>><?php echo $sub_label; ?></option>
																<?php
																
                                                            }
                                                        }
														
														$sub_value 		= NULL;
														$sub_label 		= NULL;
														$sub_selected	= NULL;
													?>
                                                    </select> 
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_category_id[]" 
														id		="sub_category_id_<?php echo $_obj_data_sub->get_id(); ?>" 
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
                                onclick	="insCategory()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                    </div>                        
                </div>
                        
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="inspection_type_list.php" target="_new">Inclusions</a></legend>                    
                            <p>Forms this question will appear in for use.</p>
      						
                            <table class="table table-striped table-hover" id="table_sub_inclusion"> 
                                <thead>
                                    
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec_inclusion">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_inclusion_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_inclusion_list->rewind(); $_obj_data_sub_inclusion_list->valid(); $_obj_data_sub_inclusion_list->next())
										{						
											$_obj_data_sub = $_obj_data_sub_inclusion_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_inclusion_item_id[]"
                                                        id		= "sub_inclusion_item_id_<?php echo $_obj_data_sub->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
                                                        if(is_object($_obj_field_source_inclusion_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_inclusion_list->rewind();	$_obj_field_source_inclusion_list->valid(); $_obj_field_source_inclusion_list->next())
                                                            {	                                                               
																$_obj_field_source_inclusion = $_obj_field_source_inclusion_list->current();
																
																$sub_value 		= $_obj_field_source_inclusion->get_id();																
																$sub_label		= $_obj_field_source_inclusion->get_label();
																$sub_selected 	= NULL;
																
																if($_obj_data_sub->get_item() === $sub_value) $sub_selected = ' selected '
																
																?>
                                                                <option value="<?php echo $sub_value; ?>"<?php echo $sub_selected ?>><?php echo $sub_label; ?></option>
																<?php
																
                                                            }
                                                        }
														
														$sub_value 		= NULL;
														$sub_label 		= NULL;
														$sub_selected	= NULL;
													?>
                                                    </select> 
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_inclusion_id[]" 
														id		="sub_inclusion_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick="delete_inclusion(this)"><span class="glyphicon glyphicon-minus"></span></button>        
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
                                onclick	="insInclusion()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                    </div>                        
                </div>
                
                <!--Ratings-->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="audit_question_rating_list.php" target="_new">Ratings</a></legend>                    
                            <p>Ratings for this audit question.</p>
      						
                            <table class="table table-striped table-hover" id="tbl_rating"> 
                                <thead>
                                    
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec_rating">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_rating_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_rating_list->rewind(); $_obj_data_sub_rating_list->valid(); $_obj_data_sub_rating_list->next())
										{						
											$_obj_data_sub = $_obj_data_sub_rating_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                	<select
                                                    	name 	= "sub_rating_item_id[]"
                                                        id		= "sub_rating_item_id_<?php echo $_obj_data_sub->get_id(); ?>"
                                                    	class	= "form-control">
														<?php
                                                        if(is_object($_obj_field_source_rating_list) === TRUE)
                                                        {        
                                                            // Generate table row for each item in list.
                                                            for($_obj_field_source_rating_list->rewind();	$_obj_field_source_rating_list->valid(); $_obj_field_source_rating_list->next())
                                                            {	                                                               
																$_obj_field_source_rating = $_obj_field_source_rating_list->current();
																
																$sub_value 		= $_obj_field_source_rating->get_id();																
																$sub_label		= $_obj_field_source_rating->get_label();
																$sub_selected 	= NULL;
																
																if($_obj_data_sub->get_item() === $sub_value) $sub_selected = ' selected '
																
																?>
                                                                <option value="<?php echo $sub_value; ?>"<?php echo $sub_selected ?>><?php echo $sub_label; ?></option>
																<?php
																
                                                            }
                                                        }
														
														$sub_value 		= NULL;
														$sub_label 		= NULL;
														$sub_selected	= NULL;
													?>
                                                    </select> 
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_rating_id[]" 
														id		="sub_rating_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick="delete_rating(this)"><span class="glyphicon glyphicon-minus"></span></button>        
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
                                onclick	="insRating()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                    </div>                        
                </div> 
                
                <!--Reference-->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>References</legend>                    
                            <p>References for this audit question. Use this section to add links, instructions, or other details customers may need to know about this item.</p>
      						
                            <table class="table table-striped table-hover" id="tbl_reference"> 
                                <thead>
                                    
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec_reference">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_reference_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_reference_list->rewind(); $_obj_data_sub_reference_list->valid(); $_obj_data_sub_reference_list->next())
										{						
											$_obj_data_sub = $_obj_data_sub_reference_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(\dc\yukon\DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>
                                                
                                                	
                                                    
                                                	<div class="form-group" id="div_sub_reference_details_<?php echo $_obj_data_sub->get_id(); ?>">
                                                        <label class="control-label col-sm-2" for="sub_reference_details_<?php echo $_obj_data_sub->get_id(); ?>">Text:</label>
                                                        <div class="col-sm-10">
                                                            <textarea 
                                                                class="form-control" 
                                                                rows="5" 
                                                                name="sub_reference_details[]" 
                                                                id="sub_reference_details_<?php echo $_obj_data_sub->get_id(); ?>"><?php echo $_obj_data_sub->get_details(); ?></textarea>
                                                        </div>
                                                    </div> 
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_reference_id[]" 
														id		="sub_reference_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick="delete_reference(this)"><span class="glyphicon glyphicon-minus"></span></button>        
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
                                onclick	="insReference()">
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
    <script src="../blair/source/javascript/dc_guid.js"></script>
	<script>
		 
		function deleteRowsub(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('table_sub_categories').deleteRow(i);
		}
		
		// Inserts a new table row on user request.
		function insCategory()
		{
			var $guid = null;
			
			$guid = dc_guid();
			
			$(".ec_category").append(
				'<tr>'
					+'<td>'
						+'<select name="sub_category_item_id[]" id="sub_category_item_id_'+ $guid +'" class="form-control">'
						+'<?php echo $category_list_options; ?>'
						+'</select>'																		
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_category_id[]" id="sub_category_id_js_'+ $guid +'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+ $guid +'" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
		}
		
		// Inclusion
		function delete_inclusion(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('table_sub_inclusion').deleteRow(i);
		}
				
		function insInclusion()
		{
			var $guid = null;
			
			$guid = dc_guid();
			
			$(".ec_inclusion").append(
				'<tr>'
					+'<td>'
						+'<select name="sub_inclusion_item_id[]" id="sub_inclusion_item_id_'+ $guid +'" class="form-control">'
						+'<?php echo $inclusion_list_options; ?>'
						+'</select>'																		
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_inclusion_id[]" id="sub_inclusion_id_js_'+ $guid +'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+ $guid +'" onclick="delete_inclusion(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
			
		}
		
		// Rating
		function delete_rating(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('tbl_rating').deleteRow(i);
		}
		
		function insRating()
		{
			var $guid = null;
			
			$guid = dc_guid();
			
			$(".ec_rating").append(
				'<tr>'
					+'<td>'
						+'<select name="sub_rating_item_id[]" id="sub_rating_item_id_'+ $guid +'" class="form-control">'
						+'<?php echo $rating_list_options; ?>'
						+'</select>'																		
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_rating_id[]" id="sub_rating_id_js_'+ $guid +'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+ $guid +'" onclick="delete_rating(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
		}
		
		// Reference
		function delete_reference(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('tbl_reference').deleteRow(i);
		}
		
		function insReference()
		{
			var $guid = null;
			
			$guid = dc_guid();
			
			$(".ec_reference").append(				
				'<tr>'
					+'<td>'
					+'<div class="form-group" id="div_sub_reference_details_'+ $guid +'">'
						+'<label class="control-label col-sm-2" for="sub_reference_details_'+ $guid +'">Text:</label>'
						+'<div class="col-sm-10">'
							+'<textarea ' 
								+'class="form-control" ' 
								+'rows="5" ' 
								+'name="sub_reference_details[]" '
								+'id="sub_reference_details_'+ $guid +'"></textarea> '
						+'</div>'
					+'</div>'
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_reference_id[]" id="sub_reference_id_js_'+ $guid +'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+ $guid +'" onclick="delete_reference(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'</tr>');
				
			tinymce.init({
                selector: '#sub_reference_details_'+$guid,
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