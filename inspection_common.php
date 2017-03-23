<?php 
	
	// Required files for this page to run.
		require(__DIR__.'/source/main.php');
	
	// Inspection type this page refers to.
		$inspection_type = 'A61F45CD-EB8A-49C2-BF63-62CE0F4F342C';
			
	// Main navigation. This will prepare 
	// the navigation bar markup 
	// common throughout app.
	
		$obj_navigation_main = new class_navigation();
		$obj_navigation_main->generate_markup_nav();
		$obj_navigation_main->generate_markup_footer();			
			
	// Record navigation. This is only to initialize
	// the record navigation object.
		$obj_navigation_rec = new \dc\recordnav\RecordNav();	
	
	// Get application form data (form type, etc.) from URL.
		$_obj_app_form_data_url = new class_application_form_data();
		$_obj_app_form_data_url->populate_from_request();
	
	// Prepare redirect url with variables. This is for
	// redirecting back to this form after a log in was
	// required or a command was executed that necessitated
	// reloading the page.
		$url_query	= new \dc\url\URLFix;
		$url_query->set_data('action', 		$obj_navigation_rec->get_action());
		$url_query->set_data('id', 			$obj_navigation_rec->get_id());
		$url_query->set_data('app_form_id',	$_obj_app_form_data_url->get_id());
	
	// Query to get the application form data.
	// This will tell us what type of form this is,
	// provide title text, instrction test, labels
	// and so on.
	
		// Set up database connection parameters.
		$db_app_form_conn_set = new ConnectConfig();
		$db_app_form_conn_set->set_name(DATABASE::NAME);
		
		// Connect to database and initialize 
		// a query object.
		$db_app_form 	= new Connect($db_app_form_conn_set);
		$query_app_form = new \dc\yukon\Database($db_app_form);
		
		// Query name or SP to call.
		$query_app_form->set_sql('{call application_form_inspection(@id = ?)}');	
		
		// Parameters for query.				
		$params_app_form = array(array($_obj_app_form_data_url->get_id(), SQLSRV_PARAM_IN));
	
		// Apply parameters and execute query.
		$query_app_form->set_params($params_app_form);
		$query_app_form->query();	
		
		// We use class objects, so set up the target
		// class to populate. Navigation results come first,
		// so skip to next result.
		$query_app_form->get_next_result();
		$query_app_form->get_line_params()->set_class_name('class_application_form_data');
		
		// Populate the class object. First we create
		// a blank. This is just in case the query produced
		// no data, and also allows our IDE to provide 
		// type hinting.		
		$_app_form_data = new class_application_form_data();		
		
		if($query_app_form->get_row_exists() === TRUE)
		{
			$_app_form_data = $query_app_form->get_line_object();
		}
		
	// User access. We will need to
	// verify user is logged in, and
	// that they have the level of 
	// access required to use this form.
	// Otherwise user will be sent to
	// log in form.
	
		$access_obj = new \dc\access\status();
		$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
		$access_obj->set_redirect($url_query->return_url());
		
		$access_obj->verify();	
		$access_obj->action();
	
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
			
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
			
			$query->set_params(array(array($_main_data->get_id(), SQLSRV_PARAM_IN)));
			$query->query();
			
			// Refresh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
			break;				
					
		case \dc\recordnav\COMMANDS::SAVE:						
	}			
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- WYSIWG text box editor -->
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
            
			<?php 
				// Main navigation bar markup.
				echo $obj_navigation_main->get_markup_nav(); 
			?>                                                                                
            
            <div class="page-header"> 
				<?php 
                    if($_app_form_data->get_title())
                    {
						?>
						
                        <h1><?php echo $_app_form_data->get_title(); ?><h1>
						
						<?php
                    }
                    
                    // Have a description?
                    if($_app_form_data->get_description())
                    {
						?>
							<p class="lead"><?php echo $_app_form_data->get_description(); ?></p>
						<?php
                    }
                ?>
            </div><!--.page-header-->
                        
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>                
                <?php //require __DIR__.'/common_inspection.php'; ?>            
                
                <input type="hidden" name="inspection_type" id="inspection_type" value="<?php echo $inspection_type; ?>">
                                       
                <!--Details-->
                
                <!--/Details-->
                
                <hr />
                <?php echo $obj_navigation_rec->get_markup(); ?>
                
                <!--Save button
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div> -->              
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