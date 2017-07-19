<?php 
		
	require(__DIR__.'/source/main.php');
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
		
	// User access.
	$access_obj = new \dc\dc\access\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
	$access_obj->get_config()->set_database($yukon_database);
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
	$db_conn_set = new ConnectConfig();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new Connect($db_conn_set);
	$query = new \dc\yukon\Database($db);
		
	$paging = new \dc\recordnav\Paging();
	$paging->set_row_max(APPLICATION_SETTINGS::PAGE_ROW_MAX);	
	
	$query->set_sql('{call account_list(@page_current 		= ?,														 
										@page_rows 			= ?)}');	
	
	$params = array(array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array(1000, 		SQLSRV_PARAM_IN));

	$query->set_param_array($params);
	$query->query_run();
	
	$query->get_line_config()->set_class_name('\data\Account');
	$_obj_data_main_list = $query->get_line_object_list();

	// --Paging
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\dc\recordnav\Paging');
	
	//$_obj_data_paging = new \dc\recordnav\Paging();
	if($query->get_row_exists()) $paging = $query->get_line_object();
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
                <h1>Accounts</h1>
                <p>Accounts allow clients to log into application, and are given levels of access based on assigned roles. The following is a list of accounts.</p>
            </div>
            
            <a href="account.php&#63;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block" title="Click here to start entering a new account."><span class="glyphicon glyphicon-plus"></span> New Account</a>
          
            <!--div class="table-responsive"-->
                <table class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Name</th>
                            <th>Notes</th>
                            <th><!--Action--></th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody>                        
                        <?php
							$account_lookup = new \dc\dc\access\lookup();
							
						
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{						
									$_obj_data_main = $_obj_data_main_list->current();
									
									
									if($_obj_data_main->get_notes() == '')
									{								
									
										$account_lookup->access_lookup($_obj_data_main->get_account());
											
										// Call update stored procedure.
										$query->set_sql('{call account_update(@id 				= ?,														 
																				@account 		= ?,
																				@department 	= ?,
																				@notes			= ?,
																				@name_f			= ?,
																				@name_l			= ?,
																				@name_m			= ?,
																				@sub_role_xml	= ?,													 
																				@log_update_by	= ?, 
																				@log_update_ip 	= ?)}');
																				
										$params = array(array($_obj_data_main->get_id(), 		SQLSRV_PARAM_IN),
													array($_obj_data_main->get_account(), 		SQLSRV_PARAM_IN),						
													array($_obj_data_main->get_department(),	SQLSRV_PARAM_IN),						
													array($_obj_data_main->get_notes(), 		SQLSRV_PARAM_IN),
													array($account_lookup->get_access_data_account()->get_name_f(), SQLSRV_PARAM_IN),
													array($account_lookup->get_access_data_account()->get_name_l(), SQLSRV_PARAM_IN),
													array($account_lookup->get_access_data_account()->get_name_m(), SQLSRV_PARAM_IN),
													array(NULL, 								SQLSRV_PARAM_IN),
													array($access_obj->get_id(), 				SQLSRV_PARAM_IN),
													array($access_obj->get_ip(), 				SQLSRV_PARAM_IN));
										
										
										
										$query->set_param_array($params);			
										$query->query_run();
										
										// Insert the roles.
										$query->set_sql("INSERT INTO tbl_account_role (fk_id, role) VALUES (?, ?)");
										
										$params = array(array($_obj_data_main->get_id(), SQLSRV_PARAM_IN),
														array('cd0a6b6c-ed15-40c0-95be-cbf0953a593e', SQLSRV_PARAM_IN));
														
										$query->set_param_array($params);			
										$query->query_run();
									}
									
                            ?>
                                        <tr>
                                            <td><?php echo $_obj_data_main->get_account(); ?></td>
                                            <td><?php if($_obj_data_main->get_name_l()) echo $_obj_data_main->get_name_l().', '.$_obj_data_main->get_name_f(); ?></td>
                                            <td><?php echo $_obj_data_main->get_notes(); ?></td>
                                            <td><a	href		="account.php?id=<?php echo $_obj_data_main->get_id(); ?>" 
                                            class		="btn btn-info"
                                            title		="View details or edit this item."
                                            ><span class="glyphicon glyphicon-eye-open"></span></a></td>
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
</script>
</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>