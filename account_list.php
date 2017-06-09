<?php 
		
	require(__DIR__.'/source/main.php');
	
	class class_filter_control
	{
		const _DATE_FORMAT  = 'Y-m-d H:i:s'; 
		
		private			
			$data_common = NULL,
			$building	= NULL,
			$floor		= NULL,
			$time_obj	= NULL,
			$time_end 	= NULL,
			$time_start	= NULL;
		
		public function __construct(\dc\chronofix\Chronofix $iChronofix)
		{
			$this->data_common = new \data\Common();
			$this->time_obj = $iChronofix;	
		}
		
		// Accessors
		public function get_data_common()
		{
			return $this->data_common;
		}
		
		public function get_id()
		{
			return $this->data_common->get_id();
		}
		
		public function get_time_end()
		{
			return $this->time_end;
		}
		
		public function get_time_start()
		{
			return $this->time_start;
		}
		
		// Mutators
		public function set_time_end($value)
		{
			// Run time through sanitizer to 
			// get a valid and uniform date/time.
			$this->time_obj->set_time($value);
			$this->time_obj->sanitize();
			
			// Set member.			
			$this->time_end = $this->time_obj->get_time();
		}
		
		public function set_time_start($value)
		{	
			// Run time through sanitizer to 
			// get a valid and uniform date/time.
			$this->time_obj->set_time($value);
			$this->time_obj->sanitize();
			
			// Set member.			
			$this->time_start = $this->time_obj->get_time();	
		}
		
		public function populate_from_request()
		{
			$this->data_common->populate_from_request();
		}
	} 
	
	// Get page configuration (title, description, query names, etc.)
	$_page_config = new \dc\application\CommonEntryConfig();	
	$_layout = $_page_config->create_config_object();
	
	// Start page cache.
	$page_obj = new \dc\cache\PageCache();
	
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	
	// Initialize time library with settings.
	$iChronofix 		= new \dc\chronofix\Chronofix();
	
	$filter_control = new class_filter_control($iChronofix);
	$filter_control->populate_from_request();
	// Establish sorting object, set defaults, and then get settings
	// from user (if any).
	$sorting = new \dc\sorting\SortControl;
	$sorting->set_sort_field(2);
	$sorting->set_sort_order(\dc\sorting\ORDER_TYPE::ASCENDING);
	$sorting->populate_from_request();
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
		
	// User access.
	$access_obj = new dc\access\status();
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
	$query = new \dc\yukon\Database();
		
	$paging = new \dc\recordnav\Paging();
	//$paging->set_row_max(APPLICATION_SETTINGS::PAGE_ROW_MAX);
	$paging->set_row_max(100);	
	
	$query->set_sql('{call '.$_layout->get_main_sql_name().'_list(@page_current 		= ?,														 
										@page_rows 			= ?,
										@sort_field			= ?,
										@sort_order			= ?)}');	
	
	$params = array(array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN),
					array($sorting->get_sort_field(), 	SQLSRV_PARAM_IN),
					array($sorting->get_sort_order(), 	SQLSRV_PARAM_IN));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('\data\Account');
	$_obj_data_main_list = $query->get_line_object_list();

	// --Paging
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('\dc\recordnav\Paging');
	
	//$_obj_data_paging = new \dc\recordnav\Paging();
	if($query->get_row_exists()) $paging = $query->get_line_object();
?>

<!DOCtype html>
<html lang="en">
    <head>
       	<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo APPLICATION_SETTINGS::NAME. ', '.$_layout->get_title(); ?></title>        
        
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
						//echo $_layout->get_description();
					}?>
                <p class="lead">This form allows you to view the complete <?php echo $_layout->get_title();?> list. Click any row to view details.</p>
            </div>
            
           <?php
				// Clickable rows. Clicking on table rows
				// should take user to a detail page for the
				// record in that row. To do this we first get
				// the base name of this file, and remove "list".
				// 
				// The detail file will always have same name 
				// without "list". Example: area.php, area_list.php
				//
				// Once we have the base name, we can use script to
				// make table rows clickable by class selector
				// and passing a completed URL (see the <tr> in
				// data table we are making clickable).
				//
				// Just to ease in development, we verify the detail
				// file exists before we actually include the script
				// and build a complete URL string. That way if the
				// detail file is not yet built, clicking on a table
				// row does nothing at all instead of giving the end
				// user an ugly 404 error.
				//
				// Lastly, if the base name exists we also build a 
				// "new item" button that takes user directly
				// to detail page with a blank record.	
			 
				$target_url 	= '#';
				$target_name	= basename(__FILE__, '_list.php').'.php';
				$target_file	= __DIR__.'/'.$target_name;				
				
				// Does the file exisit? If so we can
				// use the URL, script, and new 
				// item button.
				if(file_exists($target_file))
				{
					$target_url = $target_name.'?id_form='.$_layout->get_id();
				?>
                	<script>
						// Clickable table row.
						jQuery(document).ready(function($) {
							$(".clickable-row").click(function() {
								window.document.location = '<?php echo $target_url; ?>&id=' + $(this).data("href");
							});
						});
					</script>
                    
                    <a href="<?php echo $target_url; ?>&amp;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block" title="Click here to start entering a new item."><span class="glyphicon glyphicon-plus"></span> <?php echo $_layout->get_title(); ?></a>
                <?php
				}
				
			?>
            
            &nbsp;
            <?php echo $paging->generate_paging_markup(); ?>
            <br />
            &nbsp;
          
            <div class="table-responsive">
                <table id="tbl_account" class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th><a href="<?php echo $sorting->sort_url(1); ?>">Account <?php echo $sorting->sorting_markup(1); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(2); ?>">Name <?php echo $sorting->sorting_markup(2); ?></a></th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tfoot>
                    	<tr>
                            <th>Account</th>
                            <th>Name</th>
                            <th>Notes</th>
                        </tr>
                    </tfoot>
                    <tbody>                        
                        <?php
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{						
									$_obj_data_main = $_obj_data_main_list->current();
                            ?>
                                        <tr class="clickable-row" role="button" data-href="<?php echo $_obj_data_main->get_id(); ?>">
                                            <td><?php echo $_obj_data_main->get_account(); ?></td>
                                            <td><?php if($_obj_data_main->get_name_l()) echo $_obj_data_main->get_name_l().', '.$_obj_data_main->get_name_f(); ?></td>
                                            <td><?php echo $_obj_data_main->get_details(); ?></td>
                                        </tr>                                    
                            <?php								
                            	}
							}
                        ?>
                    </tbody>                        
                </table>
            </div>  
                        
            <?php echo $paging->get_markup(); ?>
            <br />
            &nbsp;
			<?php echo $navigation_obj->get_markup_footer(); ?>
       		<br />
            &nbsp;			
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
	//$('#tbl_account').DataTable();
});

</script>
</body>
</html>

<?php
	// Collect and output page markup.
	echo $page_obj->markup_and_flush();
?>