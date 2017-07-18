<?php 
		
	require(__DIR__.'/source/main.php');
	
	class class_filter
	{
		private			
			$update_f	= NULL,
			$update_t 	= NULL,
			$status_filter	= NULL,
			$inspector	= NULL,
			$building	= NULL;
		
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{		
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
							
				// If there is a request var with key matching
				// current method name, then the current method 
				// is a set mutator for this request var. Run 
				// it (the set method) with the request var. 
				if(isset($_GET[$key]))
				{					
					$this->$method($_GET[$key]);					
				}
			}
		}
		
		private function validateDate($date, $format = 'Y-m-d')
		{
			$d = DateTime::createFromFormat($format, $date);
			return $d && $d->format($format) == $date;
		}
		
		public function status_filter_string()
		{
			$result = '';
			
			if(is_array($this->status_filter))
			{
				$result = implode(',', $this->status_filter);
			}
			
			return $result;	
		
		}
		
		public function get_update_f()
		{
			return $this->update_f;
		}
		
		public function get_update_t()
		{
			return $this->update_t;
		}
		
		public function get_inspector_f()
		{
			return $this->inspector;
		}
		
		public function get_status_filter()
		{
			return $this->status_filter;
		}
		
		public function get_building()
		{
			return $this->building;
		}
		
		public function set_update_f($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->update_f = $value;
			}
		}
		
		public function set_update_t($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->update_t = $value;
			}
		}
		
		public function set_inspector_f($value)
		{
			if(!$value)
			{
				$value = array('00000000-0000-0000-0000-000000000000');
			}
			
			$this->inspector = $value;
		}
		
		public function set_status_filter($value)
		{		
			$this->status_filter = $value;			
		}
		
		public function set_building($value)
		{
			$this->building = $value;
		}
	}
		
	
	// Prepare redirect url with variables.
	$url_query	= new \dc\url\URLFix;
		
	// User access.
	$access_obj = new \dc\access\status();
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
	
	// Let's get the current account ID. We'll need to query
	// using the account name logged in.
	
	$query->set_sql('{call account_lookup(@account	= ?)}');
	
	$paging = new \dc\recordnav\Paging();
	$paging->set_row_max(APPLICATION_SETTINGS::PAGE_ROW_MAX);	
	
	$params = array(array($access_obj->get_account(), SQLSRV_PARAM_IN));

	

	$query->set_params($params);
	$query->query_run();
	
	$query->get_line_config()->set_class_name('\data\Account');
	$_obj_access_data_account = $query->get_line_object();
	
	//echo $access_obj->get_account();
	//echo $_obj_access_data_account->get_id();
	
	// Establish sorting object, set defaults, and then get settings
	// from user (if any).
	$sorting = new \dc\sorting\SortControl;
	$sorting->set_sort_field(6);
	$sorting->set_sort_order(\dc\sorting\ORDER_TYPE::DECENDING);
	$sorting->populate_from_request();
	
	$filter = new class_filter();
	$filter->populate_from_request();
	
	// DEBUG
	//echo 'Debug info - please ignore:<br />';
	//var_dump($filter->get_status_filter());
	//echo $filter->status_filter_string();
	
	$query->set_sql('{call inspection_list(@page_current 	= ?,														 
										@page_rows 			= ?,										
										@inspector			= ?,
										@update_from		= ?,
										@update_to			= ?,
										@status				= ?,
										@building			= ?,
										@sort_field 		= ?,
										@sort_order 		= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;		
	
	$sort_field 		= $sorting->get_sort_field();
	$sort_order 		= $sorting->get_sort_order();
	
	// In case there is no account selected, default to account logged in.
	$filter_account_argument	= $filter->get_inspector_f();
	$filter_account_string		= '';
		
	if(is_array($filter_account_argument) === FALSE)
	{
		$filter_account_argument = array($_obj_access_data_account->get_id());
	}
	
	// Let's break down the array filters before sending them to database.
	$filter_account_string = implode(",", $filter_account_argument);
	
	echo '<!--filter_account_string: '.$filter_account_string.'-->';
	
	
	$params = array(array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN), 
					array($filter_account_string, 		SQLSRV_PARAM_IN),
					array($filter->get_update_f(),		SQLSRV_PARAM_IN),
					array($filter->get_update_t(),		SQLSRV_PARAM_IN),
					array($filter->status_filter_string(),		SQLSRV_PARAM_IN),
					array($filter->get_building(),		SQLSRV_PARAM_IN),
					array($sort_field,					SQLSRV_PARAM_IN),
					array($sort_order,					SQLSRV_PARAM_IN));

	//var_dump($params);

	

	$query->set_params($params);
	$query->query_run();	
	$query->get_line_config()->set_class_name('class_common_inspection_data');
	$_obj_data_main_list = $query->get_line_object_list();
	
	// --Paging
	$query->get_next_result();
	
	$query->get_line_config()->set_class_name('\dc\recordnav\Paging');
	if($query->get_row_exists()) $paging = $query->get_line_object();
	
	// Datalist list generation.
		// Status
		$_obj_data_list_status = NULL;
		
		$query->set_sql('{call event_type_list_unpaged}');
			
		$query->query_run();
		$query->get_line_config()->set_class_name('class_common_inspection_data');
		
		//$_obj_data_list_status_list = new class_common_inspection_data();
		$_obj_data_list_status_list = $query->get_line_object_list();
	
		// Accounts (Inspectors)
		$_obj_field_source_account_list = new \data\Account();
	
		$query->set_sql('{call account_list_inspector()}');
		$query->query_run();		
		
		$query->get_line_config()->set_class_name('\data\Account');
		
		$_obj_field_source_account_list = array();
		if($query->get_row_exists() === TRUE) $_obj_field_source_account_list = $query->get_line_object_list();
		
		// Buildings
		$_obj_field_source_building_list = new \data\Area();
	
		$query->set_sql('{call building_list()}');
		$query->query_run();
		
		$query->get_line_config()->set_class_name('\data\Area');
		
		$_obj_field_source_building_list = array();
		if($query->get_row_exists() === TRUE) $_obj_field_source_building_list = $query->get_line_object_list();
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
        
        <style>
			ul.checkbox  { 
				
			 	-webkit-column-count: auto;  				
				-moz-column-count: auto;				
			  column-count: auto;			 
			  margin: 0; 
			  padding: 0; 
			  margin-left: 20px; 
			  list-style: none;			  
			} 
			
			ul.checkbox li input { 
			  margin-right: .25em; 
			  cursor:pointer;
			} 
			
			ul.checkbox li { 
			  border: 1px transparent solid; 
			  display:inline-block;
			  width:12em;			  
			} 
			
			ul.checkbox li label { 
			  margin-left: ;
			  cursor:pointer;			  
			} 
		</style>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Inspections</h1>
                <p class="lead">This is a list of inspections in the database.</p>
            </div>
            
        
            
            <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 id="h41" class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" href="#collapse_module_1"><span class="glyphicon glyphicon-filter"></span><span class="glyphicon glyphicon-menu-down pull-right"></span></a>
                        </h4>
                    </div>
                
                	<div style="" id="collapse_module_1" class="panel-collapse collapse">
                        <div class="panel-body"> 
                            <!--legend></legend-->
                            <form class="form-horizontal" role="form" id="filter" method="get" enctype="multipart/form-data">
                                                
                                <input type="hidden" name="field" value="<?php echo $sorting->get_sort_field(); ?>" />
                                <input type="hidden" name="order" value="<?php echo $sorting->get_sort_order(); ?>" />
                                
                                
                                <!--Details-->
                                <div class="form-group">                  
                                    <label class="control-label col-sm-2" for="inspector_f_0">Inspectors</label>
                                    <div class="col-sm-10">                              
                                        <select
                                            name 	= "inspector_f[]"
                                            id		= "inspector_f_0"
                                            class	= "form-control">
                                            <optgroup label="Groups">                            
                                                <option value="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" <?php if($filter->get_inspector_f() == \dc\yukon\DEFAULTS::NEW_ID) echo ' selected ' ?>>All</option>
                                            </optgroup>
                                            <optgroup label="Inspectors">
                                                <?php
                                                if(is_object($_obj_field_source_account_list) === TRUE)
                                                {        
                                                    // Generate table row for each item in list.
                                                    for($_obj_field_source_account_list->rewind();	$_obj_field_source_account_list->valid(); $_obj_field_source_account_list->next())
                                                    {	                                                               
                                                        $_obj_field_source_account = $_obj_field_source_account_list->current();
                                                        
                                                        $sub_account_value 		= $_obj_field_source_account->get_id();																
                                                        $sub_account_label		= $_obj_field_source_account->get_name_l().', '.$_obj_field_source_account->get_name_f();
                                                        $sub_account_selected 	= NULL;
                                                                
                                                        if($filter->get_inspector_f())
                                                        {
                                                            if($filter->get_inspector_f() == $sub_account_value)
                                                            {
                                                                $sub_account_selected = ' selected ';
                                                            }								
                                                        }
                                                        else
                                                        {
                                                            if($_obj_access_data_account->get_id() == $sub_account_value)
                                                            {
                                                                $sub_account_selected = ' selected ';
                                                            }
                                                        }
                                                        
                                                        ?>
                                                        <option value="<?php echo $sub_account_value; ?>" <?php echo $sub_account_selected ?>><?php echo $sub_account_label; ?></option>
                                                        <?php                                
                                                    }
                                                }
                                                ?>
                                            </optgroup>                        	
                                        </select>
                                    </div>                        
                                </div>
                                <!--/Details-->
                            
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="building">Building</label>
                                    <div class="col-sm-10">
                                        <select name="building" 
                                            id="building" 
                                            data-current="<?php echo $filter->get_building(); ?>" 
                                            data-source-url="../../libraries/inserts/facility.php" 
                                            data-extra-options='<option value="0">All Buildings</option>'
                                            data-grouped="1"
                                            class="room_search form-control">
                                            <optgroup label="Groups">                            
                                                <option value="-1" <?php if($filter->get_building() == '-1') echo ' selected ' ?>>All</option>
                                            </optgroup>
                                            <optgroup label="Buildings">
                                                <?php
                                                if(is_object($_obj_field_source_account_list) === TRUE)
                                                {        
                                                    // Generate table row for each item in list.
                                                    for($_obj_field_source_building_list->rewind();	$_obj_field_source_building_list->valid(); $_obj_field_source_building_list->next())
                                                    {	                                                               
                                                        $_obj_field_source_building = $_obj_field_source_building_list->current();
                                                        
                                                        $sub_building_code 		= $_obj_field_source_building->get_building_code();																
                                                        $sub_building_name		= $_obj_field_source_building->get_building_name();
                                                        $sub_building_selected 	= NULL;
                                                                
                                                        if($filter->get_building())
                                                        {
                                                            if($filter->get_building() == $sub_building_code)
                                                            {
                                                                $sub_building_selected = ' selected ';
                                                            }								
                                                        }
                                                        
                                                        ?>
                                                        <option value="<?php echo $sub_building_code; ?>" <?php echo $sub_building_selected; ?>><?php echo $sub_building_code .' - '.$sub_building_name; ?></option>
                                                        <?php                                
                                                    }
                                                }
                                                ?>
                                            </optgroup>                                 
                                        </select>
                                    </div>                
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="created">Updated (from)</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="update_f" 
                                            id		="update_f" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_update_f(); ?>">
                                    </div>
                                
                                    <label class="control-label col-sm-2" for="created">Updated (to)</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="update_t" 
                                            id		="update_t" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_update_t(); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="status">Status</label>
                                    <div class="col-sm-9">
                                    	<ul class="checkbox">
											<?php
                                            if(is_object($_obj_data_list_status_list) === TRUE)
                                            {									
                                                for($_obj_data_list_status_list->rewind(); $_obj_data_list_status_list->valid(); $_obj_data_list_status_list->next())
                                                {						
                                                    $_obj_data_list_status = $_obj_data_list_status_list->current();	
													
													// Reset the variables for this loop. We could
													// just reference object members directly,
													// but keeping them here makes things a bit more 
													// readable and aids in reuse of this code.
													$item_checked 	= NULL;									// Markup string to echo that marks item as checked.
													$item_array		= $filter->get_status_filter();			// Array of items from URL
													$item_id		= $_obj_data_list_status->get_id();		// ID of item in current loop.
													$item_label		= $_obj_data_list_status->get_label();	// Text label of item.
													
													// Let's find out if this item should be checked. 
													// We'll first get the array of this item passed
													// from URL. Then we find out if the ID in current
													// loop exists within array. If it does, then this
													// item is currently selected, and should be marked 
													// as checked.								
														
														// Did we get an array from URL?
														if(is_array($item_array))
														{
															// Does the current item in loop exisit in array
															// of items that were passed from URL?
															if(in_array($item_id, $item_array))							
															{
																$item_checked = ' checked ';
															}
														}
											?>                           
                                                <li>
                                                
                                                    <input 
                                                        type="checkbox" 
                                                        id  = "status_filter_<?php echo $item_id; ?>"
                                                        name= "status_filter[]" 
                                                        value="<?php echo $item_id; ?>" 
                                                        <?php echo $item_checked; ?>/>
                                                    <label 
                                                    	class="radio-inline"
														for = "status_filter_<?php echo $item_id; ?>"><?php echo $item_label; ?></label>
                                                </li>
                                           
                                            <?php
                                                }
                                            }
                                            ?>     
                                                    
                                                <!--div class="radio">
                                            
                                                    <label><input 
                                                            type="radio" 
                                                            name="status" 
                                                            value=""                                             
                                                            <?php //if($filter->get_status() == NULL) echo ' checked ';?>>All</label>
                                                </div-->                   
                                        </ul>
                                    </div>
                                </div>
                                
                                <button 
                                                type	="submit"
                                                class 	="btn btn-primary btn-block" 
                                                name	="set_filter" 
                                                id		="set_filter"
                                                title	="Apply selected filters to list."
                                                >
                                                <span class="glyphicon glyphicon-filter"></span>Apply Filters</button>       
                                    
                            </form>
                            
                                       
                        </div>
                    </div>
                </div>
            
	        
            
            <br />
            
            <a href="inspection_autoclave.php&#63;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success disabled" title="Click here to start entering a new inspection."><span class="glyphicon glyphicon-plus"></span> Autoclave</a>
            
            <a href="inspection_saa.php&#63;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success" title="Click here to start entering a new inspection."><span class="glyphicon glyphicon-plus"></span> SAA</a>
         
            <!--div class="table-responsive"-->
                <table class="table table-striped table-hover table-responsive">
                    <caption>Click a header label to change sorting order. Click any row to view or edit record details.</caption>
                    <thead>
                        <tr>                            
                            <th><a href="<?php echo $sorting->sort_url(2); ?>">Building <?php echo $sorting->sorting_markup(2); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(3); ?>">Room <?php echo $sorting->sorting_markup(3); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(4); ?>">Status <?php echo $sorting->sorting_markup(4); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(7); ?>">Type <?php echo $sorting->sorting_markup(7); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(6); ?>">Last Update <?php echo $sorting->sorting_markup(6); ?></a></th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody>                        
                        <?php
							// For looking up names from active directory.
							//$account_lookup = new \dc\access\lookup();
							
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{	
									$_obj_data_main = new class_common_inspection_data();
													
									$_obj_data_main = $_obj_data_main_list->current();
									
									echo '<!--Inspection Type: '.$_obj_data_main->get_inspection_type().'-->';
									$detail_url = NULL;
								
									// SAA
									if($_obj_data_main->get_inspection_type() == 'A61F45CD-EB8A-49C2-BF63-62CE0F4F342C')
									{
										$detail_url = 'inspection_saa.php&#63;id='.$_obj_data_main->get_id();
									}		
								
									echo '<!--Detail Url: '.$detail_url.'-->';
																	
									// Update lookup object with current account.
									//$account_lookup->access_lookup($_obj_data_main->get_account());
                            ?>
                                        <tr class="clickable-row" role="button" data-href="<?php echo $detail_url; ?>">
                                            <td><?php echo $_obj_data_main->get_building_name(); ?></td>
                                            <td><?php echo $_obj_data_main->get_room_code(); ?></td>                                            
                                            <td><?php echo $_obj_data_main->get_status_label(); ?></td>
                                            <td><?php echo $_obj_data_main->get_inspection_type_label(); ?></td>
                                            
                                    <td><?php if(is_object($_obj_data_main->get_log_update()) === TRUE) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_main->get_log_update()->getTimestamp()); ?></td>
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
  
  $(document).ready(function(event){
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