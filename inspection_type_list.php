<?php 
		
	require(__DIR__.'/source/main.php');
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
		
	// User access.
	$access_obj = new \dc\access\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
	$access_obj->set_redirect($url_query->return_url());
	
	$access_obj->verify();	
	$access_obj->action();
	
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
	// Set up database.
	$query = new \dc\yukon\Database($yukon_connection);
		
	$paging = new \dc\recordnav\Paging();
	$paging->set_row_max(APPLICATION_SETTINGS::PAGE_ROW_MAX);	
	
	$query->set_sql('{call audit_question_inclusion_list(@paged				= ?,
										@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;		
	
	$params = array(array(TRUE,							SQLSRV_PARAM_IN),
					array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN), 
					array($page_last, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($row_count, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('\data\Common');
	$_obj_data_main_list = $query->get_line_object_list();

	// Send control data from procedure to paging object.
	$paging->set_page_last($page_last);
	$paging->set_row_count_total($row_count);
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
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Inspection Types</h1>
                <p class="lead">List of inspection types. Click on a row to view or edit details of an individual item.</p>
            </div>
            
            <a href="<?php echo basename(__FILE__, '_list.php').'.php'; ?>&#63;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block" title="Click here to start entering a new item."><span class="glyphicon glyphicon-plus"></span> New Inspection Type</a>
          
            <!--div class="table-responsive"-->
                <table class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Details</th>                            
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody>                        
                        <?php
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{						
									$_obj_data_main = $_obj_data_main_list->current();
                            ?>
                                        <tr class="clickable-row" role="button" data-href="<?php echo basename(__FILE__, '_list.php').'.php'; ?>&#63;id=<?php echo $_obj_data_main->get_id(); ?>">
                                            <td><?php echo $_obj_data_main->get_label(); ?></td>
                                            <td><?php echo $_obj_data_main->get_details();; ?></td>
                                        </tr>                                    
                            <?php								
                            	}
							}
                        ?>
                    </tbody>                        
                </table>  
            <?php 
				echo $paging->generate_paging_markup();
				echo $navigation_obj->get_markup_footer(); 
			?>
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
		
		// Clickable table row.
		jQuery(document).ready(function($) {
			$(".clickable-row").click(function() {
				window.document.location = $(this).data("href");
			});
		});
	</script>
</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>