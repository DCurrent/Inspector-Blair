<?php 
	
	require(__DIR__.'/source/main.php');
	require(__DIR__.'/source/common_functions/common_security.php');	
	
	$_page_config_config = new \dc\application\CommonEntry($yukon_connection);	
	$_page_config = new \dc\application\CommonEntryConfig($_page_config_config);
	
	$_layout = $_page_config->create_config_object();

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
		header('Location: '.$_SERVER['PHP_SELF'].'?id_form='.$_layout->get_id());
		
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
		
		// List files are the name of a single record file
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
	function action_save($_layout = NULL)
	{		
		// Initialize database query object.
		$query 	= new \dc\yukon\Database($yukon_connection);
		
		// Set up account info.
		$access_obj = new \dc\access\status();
				
		// Initialize main data class and populate it from
		// post variables.
		$_main_data = new \data\AuditQuestion();						
		$_main_data->populate_from_request();
		
		$_category = new \data\AuditQuestionCategory();						
		$_category->populate_from_request();
		
		$_inclusion = new \data\AuditQuestionInclusion();						
		$_inclusion->populate_from_request();
		
		$_rating = new \data\AuditQuestionRating();						
		$_rating->populate_from_request();
		
		$_reference = new \data\AuditQuestionReference();						
		$_reference->populate_from_request();
			
		// Call update stored procedure.
		$query->set_sql('{call '.$_layout->get_main_sql_name().'_update(@param_id_list			xml				= ?, 
								@param_update_by			= ?,
								@param_update_host			= ?,
								@param_label				= ?,
								@param_details				= ?,
								@param_finding				= ?,
								@param_corrective_action	= ?,
								@param_category				= ?,
								@param_inclusion			= ?,
								@param_rating				= ?,
								@param_reference			= ?,
								@param_status				= ?)}');
												
		$params = array(array('<root><row id="'.$_main_data->get_id().'"/></root>', 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 			SQLSRV_PARAM_IN),
					array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
					array($_main_data->get_details(),		SQLSRV_PARAM_IN),
					array($_main_data->get_finding(),		SQLSRV_PARAM_IN),
					array($_main_data->get_corrective_action(),		SQLSRV_PARAM_IN),
					array($_category->xml(),				SQLSRV_PARAM_IN),
					array($_inclusion->xml(),				SQLSRV_PARAM_IN),
					array($_rating->xml(),					SQLSRV_PARAM_IN),
					array($_reference->xml(), 				SQLSRV_PARAM_IN),
					array($_main_data->get_status(),		SQLSRV_PARAM_IN));
		
		var_dump($params);
		
		$query->set_params($params);			
		$query->query_run();
		
		// Repopulate main data object with results from merge query.
		// We can use common data here because all we need
		// is the ID for redirection.
		$query->get_line_config()->set_class_name($_layout->get_main_object_name());
		$_main_data = $query->get_line_object();
		
		// Now that save operation has completed, reload page using ID from
		// database. This ensures the ID is always up to date, even with a new
		// or copied record.
		header('Location: '.$_SERVER['PHP_SELF'].'?id_form='.$_layout->get_id().'&id='.$_main_data->get_id());
	}
	
	
			
	
	///////////////
	
	
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
	
	// Initialize database query object.
	$query 	= new \dc\yukon\Database($yukon_connection);
	
	// Class name has to be populated into local var to
	// be instantiated.
	$main_data_class_name = $_layout->get_main_object_name();
	
	// Initialize a blank main data object.
	$_main_data = new $main_data_class_name();	
		
	// Populate from request so that we have an 
	// ID and KEY ID (if nessesary) to work with.
	$_main_data->populate_from_request();
	
	// Set up primary query with parameters and arguments.
	$query->set_sql('{call '.$_layout->get_main_sql_name().'(@param_filter_id = ?,
									@param_filter_id_key = ?)}');
	$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($_main_data->get_id_key(), 	SQLSRV_PARAM_IN));

	// Apply arguments and execute query.
	$query->set_params($params);
	$query->query_run();
	
	// Get navigation record set and populate navigation object.		
	$query->get_line_config()->set_class_name('\dc\recordnav\RecordNav');	
	if($query->get_row_exists() === TRUE) $obj_navigation_rec = $query->get_line_object();	
	
	// Get primary data record set.	
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name($_layout->get_main_object_name());	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();	
	
	// Sub table (category) generation
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_data_sub_category_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_category_list = $query->get_line_object_list();
	
	// Sub table (inclusion) generation
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_data_sub_inclusion_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_inclusion_list = $query->get_line_object_list();
	
	// Sub table (rating) generation
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_data_sub_rating_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_rating_list = $query->get_line_object_list();
		
	// Sub table (reference) generation.
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\data\Common');
	
	$_obj_data_sub_reference_list = new SplDoublyLinkedList();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_reference_list = $query->get_line_object_list();
	
	// Source List Generation	
		// Categories
		$query->set_sql('{call audit_question_category_list(@page_current = ?)}');
		$params = array(array(-1,			SQLSRV_PARAM_IN));

		$query->set_params($params);
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
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
		$query->set_sql('{call audit_question_inclusion_list(@page_current = ?)}');											
		$params = array(array(-1,			SQLSRV_PARAM_IN));

		$query->set_params($params);
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
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
		$query->set_sql('{call audit_question_rating_list(@page_current = ?)}');											
		$params = array(array(-1,			SQLSRV_PARAM_IN));

		$query->set_params($params);
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Common');
		
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
        <title><?php echo APPLICATION_SETTINGS::NAME; 
		
			// Add page title, if any.
			if($_layout->get_title())
			{
				echo ', '.$_layout->get_title();
            }?></title>        
        
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
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
                <h1><?php
					// Add page title, if any.
					if($_layout->get_title())
					{
						echo $_layout->get_title();
					}?></h1>
                <?php
					// Add page description, if any.
					if($_layout->get_description())
					{
						echo $_layout->get_description();
					}?>
                                
				<?php
					// If this isn't the active version, better alert user.
					if(!$_main_data->get_active() && $_main_data->get_id_key())
					{
					?>
						<div class="alert alert-warning">
							<strong>Notice:</strong> You are currently viewing an inactive revision of this record. Saving will make this the active revision. To return to the currently active revision without saving, click <a href="<?php echo $_SERVER['PHP_SELF'].'?id_form='.$_layout->get_id().'&amp;id='.$_main_data->get_id(); ?>">here</a>.
						</div>
					<?php
					}
				?>
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
                                <a id="revision" href = "common_version_list.php?id_form=<?php echo $_layout->get_id(); ?>&amp;id=<?php echo $_main_data->get_id();  ?>"
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
                
                <div class="form-group" id="fg_status">	
					<label class="control-label col-sm-2" for="status" title="">Status</label>								
					<div class="col-sm-10">
						<label class="radio-inline"><input type="radio" 
							name	= "status"
							id		= "status_1"
							value	= "1"
							required
							<?php if($_main_data->get_status()){ echo ' checked'; } ?>>Active</label>
						&nbsp;
						<label class="radio-inline"><input type	= "radio"							
							name	= "status" 
							id		= "status_0"
							value	= "0"
							required
							<?php if(!$_main_data->get_status()){ echo ' checked'; } ?>>Inactive</label>   
					</div>
				</div><!--#fg_status-->
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="finding">Finding:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control wysiwyg" rows="5" name="finding" id="finding"><?php echo $_main_data->get_finding(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="corrective_action">Corrective Action:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control wysiwyg" rows="5" name="corrective_action" id="corrective_action"><?php echo $_main_data->get_corrective_action(); ?></textarea>
                	</div>
                </div>
                
                <!-- Categories -->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="./?id_form=1548&amp;list=1">Categories</a></legend>                    
                            <p class="small">Categories assigned to this question.</p>
      						
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
                                                    	name 	= "sub_category_item[]"
                                                        id		= "sub_category_item_<?php echo $_obj_data_sub->get_id(); ?>"
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
                <!-- /Categories -->
                
                <!-- Inclusions -->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>Inclusions</legend>                    
                            <p class="small">Forms this question will appear in for use.</p>
      						
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
                                                    	name 	= "sub_inclusion_item[]"
                                                        id		= "sub_inclusion_item_<?php echo $_obj_data_sub->get_id(); ?>"
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
                <!-- /Inclusions -->
                
                <!-- Ratings -->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend><a href="./?id_form=1172&amp;list=1">Ratings</a></legend>                    
                            <p class="small">Ratings for this audit question.</p>
      						
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
                                                    	name 	= "sub_rating_item[]"
                                                        id		= "sub_rating_item_<?php echo $_obj_data_sub->get_id(); ?>"
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
                <!-- /Ratings -->
                
                <!-- Reference -->
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
                                                	<div class="form-group" id="div_sub_reference_<?php echo $_obj_data_sub->get_id(); ?>">                                    
                                                        <div class="col-sm-12">
                                                            <textarea 
                                                                class="form-control wysiwyg" 
                                                                rows="5" 
                                                                name="sub_reference_item[]" 
                                                                id="sub_reference_item_<?php echo $_obj_data_sub->get_id(); ?>"><?php echo $_obj_data_sub->get_details(); ?></textarea>
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
                <!-- /Reference -->
                
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
        <script src="source/javascript/dc_guid.js"></script>
		<script>
            //Google Analytics Here// 
        
            $(document).ready(function(){
            });
			
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
							+'<select name="sub_category_item[]" id="sub_category_item_'+ $guid +'" class="form-control">'
							+'<?php echo $category_list_options; ?>'
							+'</select>'																		
						+'</td>'					
						+'<td colspan="2">'
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
							+'<select name="sub_inclusion_item[]" id="sub_inclusion_item_'+ $guid +'" class="form-control">'
							+'<?php echo $inclusion_list_options; ?>'
							+'</select>'																		
						+'</td>'					
						+'<td colspan="2">'
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
							+'<select name="sub_rating_item[]" id="sub_rating_item_'+ $guid +'" class="form-control">'
							+'<?php echo $rating_list_options; ?>'
							+'</select>'																		
						+'</td>'					
						+'<td colspan="2">'
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
						+'<div class="form-group" id="div_sub_reference_'+ $guid +'">'
							//+'<label class="control-label col-sm-2" for="sub_reference_details_'+ $guid +'">Text</label>'
							+'<div class="col-sm-12">'
								+'<textarea ' 
									+'class="form-control" ' 
									+'rows="5" ' 
									+'name="sub_reference_item[]" '
									+'id="sub_reference_item_'+ $guid +'"></textarea> '
							+'</div>'
						+'</div>'
						+'</td>'					
						+'<td colspan="2">'
							+'<input type="hidden" name="sub_reference_id[]" id="sub_reference_id_js_'+ $guid +'" value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
							+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+ $guid +'" onclick="delete_reference(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
						+'</td>'
					+'</tr>');
					
				tinymce.init({
					selector: '#sub_reference_item_'+$guid,
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