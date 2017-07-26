<?php 
	
	require(__DIR__.'/source/main.php');
	require(__DIR__.'/source/common_functions/common_security.php');	
	
	$_page_config_config = new \dc\application\CommonEntry($yukon_connection);	
	$_page_config = new \dc\application\CommonEntryConfig($_page_config_config);
	
	$_layout = $_page_config->create_config_object();
	
	// Delete current record.
	function action_delete($_layout = NULL, $database)
	{
		// Set up account info.
		$access_obj = new \dc\access\status();
		
		// Initialize main data class and populate it from
		// post variables. All we need is the ID, so
		// common data will work here.
		$_main_data = new \data\Common();						
		$_main_data->populate_from_request();
			
		// Call and execute delete SP.
		$database->set_sql('{call master_delete(@id = ?,													 
								@update_by	= ?, 
								@update_ip 	= ?)}');
		
		$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 			SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
		
					
		$database->set_param_array($params);
		$database->query_run();	
		
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
	function action_save($_layout = NULL, $database)
	{		
		// Set up account info.
		$access_obj = new \dc\access\status();
				
		// Initialize main data class and populate it from
		// post variables.
		$_main_data = new \data\Inspection();						
		$_main_data->populate_from_request();
		
		$_sub_area_data = new \data\Area();						
		$_sub_area_data->populate_from_request();
		
		$_sub_party_data = new \data\InspectionParty();						
		$_sub_party_data->populate_from_request();
		
		$_sub_visit_data = new \data\InspectionVisitSub();						
		$_sub_visit_data->populate_from_request();
		
		$_sub_detail_data = new \data\InspectionDetailSub();						
		$_sub_detail_data->populate_from_request();
			
		// Call update stored procedure.
		$database->set_sql('{call '.$_layout->get_main_sql_name().'_update(@param_id_list			= ?,
												@param_update_by	= ?, 
												@param_update_host 	= ?,										 
												@param_label 		= ?,
												@param_details 		= ?,
												@param_type			= ?,
												@param_area			= ?,
												@param_party		= ?,
												@param_visit		= ?,
												@param_detail		= ?)}');
												
		$params = array(array('<root><row id="'.$_main_data->get_id().'"/></root>', 		SQLSRV_PARAM_IN),
					array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
					array($access_obj->get_ip(), 			SQLSRV_PARAM_IN),
					array($_main_data->get_label(), 		SQLSRV_PARAM_IN),						
					array($_main_data->get_details(),		SQLSRV_PARAM_IN),
					array('<root><row id="'.$_layout->get_id().'"/></root>',				SQLSRV_PARAM_IN),
					array('<root><row id="'.$_sub_area_data->get_room_code().'"/></root>',	SQLSRV_PARAM_IN),
					array($_sub_party_data->xml(),	SQLSRV_PARAM_IN),
					array($_sub_visit_data->xml(),	SQLSRV_PARAM_IN),
					array($_sub_detail_data->xml(),	SQLSRV_PARAM_IN));
		
		// For debugging.
		//var_dump($params);
		//exit;
		
		$database->set_param_array($params);
		$database->query_run();
		
		// Repopulate main data object with results from merge query.
		// We can use common data here because all we need
		// is the ID for redirection.
		$database->get_line_config()->set_class_name($_layout->get_main_object_name());
		$_main_data = $database->get_line_object();
		
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
			
			action_delete($_layout, $yukon_database);	
			break;				
					
		case \dc\recordnav\COMMANDS::SAVE:
			
			action_save($_layout, $yukon_database);			
			break;			
	}
	
	// Class name has to be populated into local var to
	// be instantiated.
	$main_data_class_name = $_layout->get_main_object_name();
	
	// Initialize a blank main data object.
	$_main_data = new $main_data_class_name();	
		
	// Populate from request so that we have an 
	// ID and KEY ID (if nessesary) to work with.
	$_main_data->populate_from_request();
	
	// Set up primary query with parameters and arguments.
	$yukon_database->set_sql('{call '.$_layout->get_main_sql_name().'(@param_filter_id = ?,
									@param_filter_id_key = ?,
									@param_filter_type = ?)}');
	$params = array(array($_main_data->get_id(), 		SQLSRV_PARAM_IN),
					array($_main_data->get_id_key(), 	SQLSRV_PARAM_IN),
					array($_layout->get_id(), 			SQLSRV_PARAM_IN));

	// Apply arguments and execute query.
	$yukon_database->set_param_array($params);
	$yukon_database->query_run();
	
	// Get navigation record set and populate navigation object.		
	$yukon_database->get_line_config()->set_class_name('\dc\recordnav\RecordNav');	
	if($yukon_database->get_row_exists() === TRUE) $obj_navigation_rec = $yukon_database->get_line_object();	
	
	// Get primary data record set.	
	$yukon_database->get_next_result();
	
	$yukon_database->get_line_config()->set_class_name($_layout->get_main_object_name());	
	if($yukon_database->get_row_exists() === TRUE) $_main_data = $yukon_database->get_line_object();
	
	// Sub - Party.
	$yukon_database->get_next_result();
	
	$yukon_database->get_line_config()->set_class_name('\data\Account');
	
	$_obj_data_sub_party_list = new SplDoublyLinkedList();
	if($yukon_database->get_row_exists()) $_obj_data_sub_party_list = $yukon_database->get_line_object_list();		
	
	// Sub - Visit
	$yukon_database->get_next_result();
	
	$yukon_database->get_line_config()->set_class_name('\data\InspectionVisit');
	
	$_obj_data_sub_visit_list = new SplDoublyLinkedList();
	if($yukon_database->get_row_exists()) $_obj_data_sub_visit_list = $yukon_database->get_line_object_list();
	
	// Sub - Details
	$yukon_database->get_next_result();
	
	$yukon_database->get_line_config()->set_class_name('\data\InspectionDetail');
	
	$_obj_data_sub_detail_list = new SplDoublyLinkedList();
	if($yukon_database->get_row_exists()) $_obj_data_sub_detail_list = $yukon_database->get_line_object_list();
	
	// Sub - Area
	$yukon_database->get_next_result();
	
	$yukon_database->get_line_config()->set_class_name('\data\Area');
	
	$_obj_data_sub_area_list = new \data\area();
	if($yukon_database->get_row_exists()) $_obj_data_sub_area_list = $yukon_database->get_line_object();
	
	
	// Item lists....
	
	// Categories
		$yukon_database->set_sql('{call audit_question_category_list_for_inspection_entry(@param_page_current 		= ?,
															@param_page_rows								= ?,
															@param_filter_inclusion							= ?)}');											
		$page_last 	= NULL;
		$row_count 	= NULL;		
		
		$inspection_type = $_layout->get_id();
		
		$params = array(array(-1,			SQLSRV_PARAM_IN),
						array(NULL,			SQLSRV_PARAM_IN),
						array($inspection_type,			SQLSRV_PARAM_IN));

		$yukon_database->set_param_array($params);
		$yukon_database->query_run();
		
		$yukon_database->get_line_config()->set_class_name('\data\Common');
		
		$_obj_field_source_category_list = new SplDoublyLinkedList();
		if($yukon_database->get_row_exists() === TRUE) $_obj_field_source_category_list = $yukon_database->get_line_object_list();
		
		// Generate markup for new insert. Markup for existing records are generated per each
		// record loop to 'select' the current record value.
		$category_list_options = '<optgroup label="Groups"><option value="'.\dc\yukon\DEFAULTS::NEW_ID.'">All</option></optgroup><optgroup label="Categories">';
				
		for($_obj_field_source_category_list->rewind();	$_obj_field_source_category_list->valid(); $_obj_field_source_category_list->next())
		{	                                                               
			$_obj_field_source_category = $_obj_field_source_category_list->current();					
			
			$category_list_options .= '<option value="'.$_obj_field_source_category->get_id().'">'.$_obj_field_source_category->get_label().'</option>';					
		}
		
		$category_list_options .= '</optgroup>';

		//////////
		// Audit item query. Since we are constructing markup as we go, 
		// there's no getting around multiple executions, so we'll 
		// prepare the query here with bound parameters for
		// maximum speed and efficiency.
		
		// Bound parameters.
		$query_audit_items_params			= array();
		$query_audit_items_param_category 	= NULL;		
		
		// Set up a query object and send SQL string.
		$query_audit_items = new \dc\yukon\Database($yukon_connection);
		$query_audit_items->set_sql('{call inspection_question_list_select(@category 	= ?,
															@inclusion	= ?)}');
		
		// Set up bound parameters.
		$query_audit_items_params = array(array(&$query_audit_items_param_category, SQLSRV_PARAM_IN),
										array(&$inspection_type, SQLSRV_PARAM_IN));
		
		// Prepare query for execution.
		$query_audit_items->set_param_array($query_audit_items_params);
		$query_audit_items->query_prepare();
				
		// Generate a list for new insert. List for existing records are generated per each
		// record loop to 'select' the current record value.
		$correction_list_options = '<option value="'.\dc\yukon\DEFAULTS::NEW_ID.'">Select Item</option>';
		
		// Interate through each category. At every loop we will set our bound 
		// category parameter and execute the item query.
		for($_obj_field_source_category_list->rewind();	$_obj_field_source_category_list->valid(); $_obj_field_source_category_list->next())
		{	                                                               
			$_obj_field_source_category = $_obj_field_source_category_list->current();					
			
			// Add current category to markup as an option group.
			$correction_list_options .= '<optgroup label="'.$_obj_field_source_category->get_label().'">';
			
			// Set bound parameter and execute prepared query. 
			$query_audit_items_param_category = $_obj_field_source_category->get_id();			
			$query_audit_items->query_execute();
			
			// Set class object we will push rows from datbase into.
			$query_audit_items->get_line_config()->set_class_name('\data\AuditQuestion');
			
			// Establish linked list of objects and populate with rows assuming that 
			// rows were returned. 
			$_obj_data_list_saa_correction_list = new SplDoublyLinkedList();
			if($query_audit_items->get_row_exists() === TRUE) $_obj_data_list_saa_correction_list = $query_audit_items->get_line_object_list();
			
			// Now loop over all items returned from our prepared query execution.
			for($_obj_data_list_saa_correction_list->rewind();	$_obj_data_list_saa_correction_list->valid(); $_obj_data_list_saa_correction_list->next())
			{	                                                               
				$_obj_data_list_saa_correction = $_obj_data_list_saa_correction_list->current();
				
				// Place finding text into a holding variable for text work.
				$data_list_saa_correction_finding = $_obj_data_list_saa_correction->get_finding();
				
				// Remove all HTML tags and single quotes.
				$data_list_saa_correction_finding = strip_tags($data_list_saa_correction_finding);
				$data_list_saa_correction_finding = htmlspecialchars($data_list_saa_correction_finding, ENT_QUOTES);					
				
				$correction_list_options .= '<option value="'.$_obj_data_list_saa_correction->get_id().'">'.$data_list_saa_correction_finding.'</option>';					
			}
			
			// Close the option group markup for this category.
			$correction_list_options .= '</optgroup>';
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
        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
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
                
                <?php require __DIR__.'/common_inspection.php'; ?>
                 
                <hr />
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup(); ?>
                	</div>
                </div>               
            </form>
            
            <?php echo $obj_navigation_main->generate_markup_footer(); ?>
        </div><!--container-->        
		<script src="source/javascript/verify_save.js"></script>
		<script>
			$('body').on('keydown', 'input, select, textarea', function(e) {
				var self = $(this)
				  , form = self.parents('form:eq(0)')
				  , focusable
				  , next
				  ;
				if (e.keyCode == 13) {
					focusable = form.find('input,a,select,button,textarea').filter(':visible');
					next = focusable.eq(focusable.index(this)+1);
					if (next.length) {
						next.focus();
					} else {
						form.submit();
					}
					return false;
				}
			});
			
            //Google Analytics Here// 
        
            $(document).ready(function(){
            });
        </script>
	</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>