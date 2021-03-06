<!--Include: <?php echo __FILE__ . ", Last update: " . date(DATE_ATOM,filemtime(__FILE__)); ?>-->

	<style>
		.checkbox-inline + .checkbox-inline, .radio-inline + .radio-inline {
		  margin-left: 0;
		}
		.columns label.radio-inline, .columns label.checkbox-inline {
		  min-width: 190px;
		  vertical-align: top;
		  width: 30%;
		}
	</style>

		<?php 			
			require __DIR__.'/model_party.php';
			
			// List queries
			// --Status
			
			// Set up database.
			$form_common_query = new \dc\yukon\Database($yukon_connection);			
			
			// --Accounts (Inspector)
			$_obj_field_source_account_list = new \data\Account();
		
			$form_common_query->set_sql('{call account_list_inspector()}');
			$form_common_query->query_run();
			
			$form_common_query->get_line_config()->set_class_name('\data\Account');
			
			$_obj_field_source_account_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_field_source_account_list = $form_common_query->get_line_object_list();	
			
			// --Accounts (Party)
			$_obj_field_source_party_list = new \data\Account();
		
			$form_common_query->set_sql('{call account_list_party()}');
			$form_common_query->query_run();
			
			$yukon_database->get_line_config()->set_class_name('\data\Account');
			
			$_obj_field_source_party_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_field_source_party_list = $form_common_query->get_line_object_list();		
			
			// --Event type
			$_obj_data_list_event_type_list = new \data\Common();
		
			$form_common_query->set_sql('{call inspection_status_list()}');
			$form_common_query->query_run();
			
			$form_common_query->get_line_config()->set_class_name('\data\Common');
			
			$_obj_data_list_event_type_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_data_list_event_type_list = $form_common_query->get_line_object_list();
				
			?>
        
        <!-- Location display. -->
        <div class="form-group">
            <label class="control-label col-sm-2" for="building">Location</label>  
            <div class="col-sm-10 area-survey-row-progress" id="area-survey-row-progress"></div>          	
			<div class="col-sm-10 area-survey">            		
			</div>
        </div>		
        
        <!-- Area code entry -->  
        <?php require __DIR__.'/model_location.php'; ?>
        
        <div class="form-group area-entry-container">
            <label class="control-label col-sm-2" for="room_code">Area Code</label>
            <div class="col-sm-8">
                <input type="text" class="form-control area-entry-field"  name="room_code" id="room_code" placeholder="Room code" value="<?php echo $_obj_data_sub_area_list->get_room_code(); ?>">
            </div>
            
            <div class="col-sm-1">
              <a href="#"
                    class		="btn btn-sm btn-info btn-responsive building_search pull-right" 
                    data-toggle	="modal"
                    title		="Find a room barcode."
                    
                    ><span class="glyphicon glyphicon-search"></span></a>
            </div>
        </div>
        
        <script>
			// Add a location.
			function area_survey_row_add($select_target = null)
			{
				var $id;			// Guid for elements.
				var $url_base;		// Base file url.
				var $url_request;	// Request vars sent with URL.
				var $url_complete;	// File with request elements.

				// Guid - Will be concatenated to IDs of the elements
				// we are appending. This ensures the appended IDs will
				// be unique without us having to track a glocal variable.
				$id = dc_klondike_guid();

				// Base file name will we load with ajax.
				$url_base = 'area_survey.php';

				// Prepare quest vars.
				// -- Guid for elements.
				$url_request = '?id_guid=' + $id;

				// -- Option to be pre selected.
				$url_request = $url_request + '&filter_area_code=' + $select_target;

				// Complete the URL.
				$url_complete = $url_base + $url_request;

				// Hide the control buttons and show loading alert.
				$('.area-survey-row-progress').empty().append('<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">Loading...</div>');

				$('.area-entry-container').hide();

				// Append a new container <div> and load contents of target PHP
				// file. When complete, make the controls visible and remove 
				// progress bar.
				$('.area-survey').empty().append($('<div id="area-survey-row-container-' + $id + '" class="area-survey-row-container" />').load($url_complete, function(){
					$('.area-survey-row-progress').empty();
					$('.area-entry-container').show();
				}));
			}
			
			// area/room entry change listener.
			$('.area-entry-field').change(function(){
		
				var $value = $('.area-entry-field').val();
				area_survey_row_add($value);
			});
			
			// Listener for area search dialog insert key.
			$('.room_code_insert').click((function() {
	
				var $value = $('.room_code_search').val();

				$('input[name="room_code"]').val($value);	
				area_survey_row_add($value)
			}));
			
		</script>
        <?php         
            // load the list with ID pre-selected.
			$room_code = trim($_obj_data_sub_area_list->get_room_code());			
		?>
		<script>
			area_survey_row_add('<?php echo $room_code; ?>');
		</script>
				
        
        
        <!-- Parties -->
        <div class="form-group">
       	  <div class="col-sm-2">
          </div>                       
          <fieldset class="col-sm-10" >
                <legend>Party Review</legend> 
                                                                  
                <table class="table table-striped table-hover table-condensed" id="tbl_sub_party"> 
                    <thead>
                        <tr>
                            <th><!-- Responsible Party --></th>
                            <th><!-- Party search button --></th>
                            <th><!-- ID, Delete Button --></th>                            
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody id="tbody_party" class="parties">                        
                        <?php                              
                        if(is_object($_obj_data_sub_party_list) === TRUE)
                        {        
                            // Generate table row for each item in list.
                            for($_obj_data_sub_party_list->rewind(); $_obj_data_sub_party_list->valid(); $_obj_data_sub_party_list->next())
                            {						
                                $_obj_data_sub_party = $_obj_data_sub_party_list->current();
                            
                                // Blank IDs will cause a database error, so make sure there is a
                                // usable one here.
                                if(!$_obj_data_sub_party->get_item()) $_obj_data_sub_party->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                
                            ?>
                                <tr>
                                    <td>
                                        <a href="./?id_form=1256&amp;id=<?php echo $_obj_data_sub_party->get_item(); ?>" target="_blank"><?php echo $_obj_data_sub_party->get_name_l().', '.$_obj_data_sub_party->get_name_f(); ?></a>
                                    </td>
                                    <td>
                                    	<!-- Party search goes here on new row adds  -->
                                    </td>                                  
                                    <td style="width:1px">   
                                    	<input 
                                            type	="hidden" 
                                            name	="sub_party_party[]" 
                                            id		="sub_party_party_<?php echo $_obj_data_sub_party->get_item(); ?>" 
                                            value	="<?php echo $_obj_data_sub_party->get_item(); ?>" />
                                            
                                        <button 
                                            type	="button" 
                                            class 	="btn btn-danger btn-sm pull-right" 
                                            name	="sub_party_row_del" 
                                            id		="sub_party_row_del_<?php echo $_obj_data_sub_party->get_item(); ?>" 
                                            onclick="deleteRow_sub_party(this)"><span class="glyphicon glyphicon-minus"></span></button>        
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
                    id		="row_add_party"
                    title	="Add new item."
                    onclick	="insRow_party()">
                    <span class="glyphicon glyphicon-plus"></span></button>
                
            </fieldset>    
        </div>  
                               
        <!--<div class="form-group">
            <label class="control-label col-sm-2" for="name">Label:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control"  name="label" id="label" placeholder="Inspection Title" value="<?php echo $_main_data->get_label(); ?>">
            </div>
        </div>-->
        
        <!--Details-->
            <div class="form-group">                    	
                <div class="col-sm-offset-2 col-sm-10">
                    <fieldset>
                        <legend>Findings</legend>                                
                        <table class="table table-striped table-hover table-condensed" id="tbl_sub_finding"> 
                            <thead>
                            </thead>
                            <tfoot>
                            </tfoot>
                            <tbody class="tbody_finding">                        
                                <?php                              
                                if(is_object($_obj_data_sub_detail_list) === TRUE)
                                {   
                                    $details_counter = 0;
									
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
                                     
                                    // Generate table row for each item in list.
                                    for($_obj_data_sub_detail_list->rewind(); $_obj_data_sub_detail_list->valid(); $_obj_data_sub_detail_list->next())
                                    {	
										$details_counter++;
										
                                        $_obj_data_sub_detail = $_obj_data_sub_detail_list->current();
                                    
                                        // Blank IDs will cause a database error, so make sure there is a
                                        // usable one here.
                                        if(!$_obj_data_sub_detail->get_id_key()) $_obj_data_sub_party->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                        
                                    ?>
                                        <tr>
                                            <td> 
                                            	<!-- Correction/finding -->
                                                <div class="form-group">
                                                    <label class="control-label col-sm-1" for="sub_detail_correction_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" title="Finding."><span class="glyphicon glyphicon-wrench"></span></label>
                                                    <div class="col-sm-11">	
                                                    	<?php echo $_obj_data_sub_detail->get_finding(); ?>								
                                                        <input
                                                            type	= "hidden"
                                                            name 	= "sub_detail_correction[]"
                                                            id		= "sub_detail_correction_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" 	
                                                            value 	= <?php echo $_obj_data_sub_detail->get_correction(); ?> />								   
                                                    </div>
                                                </div> 
                                                                                      	                                           		
                                           		<!-- Complete toggles. Current value: <?php echo $_obj_data_sub_detail->get_complete(); ?>-->
												<div class="form-group">	
													<label class="control-label col-sm-1" for="sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" title="Complete: Select to indicate this particular correction has been rectified."><span class="glyphicon glyphicon-ok"></span></label>								
													<div class="col-sm-11">
														<label class="radio-inline"><input type="radio" 
															class	= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>"
															name	= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>"
															id		= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>_1"
															value	= "1"
															required
															<?php if($_obj_data_sub_detail->get_complete()){ echo ' checked'; } ?>><span class="glyphicon glyphicon-thumbs-up text-success" style="font-size:large;"></span></label>
														&nbsp;
														<label class="radio-inline"><input type	= "radio" 
															class	= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>"
															name	= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" 
															id		= "sub_detail_complete_<?php echo $_obj_data_sub_detail->get_id_key(); ?>_0"
															value	= "0"
															required
															<?php if(!$_obj_data_sub_detail->get_complete()){ echo ' checked'; } ?>><span class="glyphicon glyphicon-thumbs-down text-danger" style="font-size:large;"></span></label>   
													</div>
												</div>
                                           
                                           		<!-- Details -->
                                                <?php
													$_common_details_class_add = NULL;
										
													if($_obj_data_sub_detail->get_details())
													{
														$_common_details_class_add = 'style="background-color:#dff0d8"';
													}
												?>
                                                        <div class="form-group">
                                                          	<div class="col-sm-12" id="details_container">
																<div class="panel panel-default">
																	<div class="panel-heading" <?php echo $_common_details_class_add; ?>>
																		<h4 id="h41_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" class="panel-title">
																		<a class="accordion-toggle" data-toggle="collapse" href="#collapse_module_<?php echo $_obj_data_sub_detail->get_id_key(); ?>"><span class="glyphicon glyphicon-list-alt"></span><span class="glyphicon glyphicon-menu-down pull-right"></span></a>
																		</h4>
																	</div>

																<div style="" id="collapse_module_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" class="panel-collapse collapse">
																		<div class="panel-body"> 
																			<textarea class="form-control wysiwyg" 
																				rows="5" 
																				name="sub_detail_details[]" 
																				id="sub_detail_details_<?php echo $_obj_data_sub_detail->get_id_key(); ?>"><?php echo $_obj_data_sub_detail->get_details(); ?></textarea>                        
																		</div>
																	</div>
																</div>
															</div><!-- #details_container -->
                                                        </div>
                                            </td>               
                                                  
                                            <td>
                                            	                                                                    
                                            	<input 
                                                    type	="hidden" 
                                                    name	="sub_detail_id[]" 
                                                    id		="sub_detail_id_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" 
                                                    value	="<?php echo $_obj_data_sub_detail->get_id_key(); ?>" />
                                                
                                                <button 
                                                    type	="button" 
                                                    class 	="btn btn-danger btn-sm pull-right" 
                                                    name	="sub_detail_row_del" 
                                                    id		="sub_detail_row_del_<?php echo $_obj_data_sub_detail->get_id_key(); ?>" 
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
 
 		<div class="form-group" id="fg_audits">       
       	  <div class="col-sm-2">
          </div>                
          <fieldset class="col-sm-10">
                <legend>Audits</legend>
                                                
                <table class="table table-striped table-hover table-condensed" id="tbl_sub_visit"> 
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>By</th>  
                            <th>Time</th>
                            <th></th>                            
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody id="tbody_visit" class="visit">                        
                        <?php                              
                        if(is_object($_obj_data_sub_visit_list) === TRUE)
                        {        
                            // Generate table row for each item in list.
                            for($_obj_data_sub_visit_list->rewind(); $_obj_data_sub_visit_list->valid(); $_obj_data_sub_visit_list->next())
                            {						
                                $_obj_data_sub_visit = $_obj_data_sub_visit_list->current();
                            
                                // Blank IDs will cause a database error, so make sure there is a
                                // usable one here.
                                if(!$_obj_data_sub_visit->get_id_key()) $_obj_data_sub_visit->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                
                            ?>
                                <tr>
                                    <td><a href="audit_type.php?id_form=1599&amp;id=<?php echo $_obj_data_sub_visit->get_visit_type(); ?>" target="_blank"><?php echo $_obj_data_sub_visit->get_visit_type_label(); ?></a>
                                        <input type="hidden"
                                            name 	= "sub_visit_type[]"
                                            id		= "sub_visit_type_<?php echo $_obj_data_sub_visit->get_id_key(); ?>"
                                            value	="<?php echo $_obj_data_sub_visit->get_visit_type(); ?>" />                                       
                                    </td>  
                                    
                                    <td><a href="./?id_form=1256&amp;id=<?php echo $_obj_data_sub_visit->get_visit_by(); ?>" target="_blank"><?php echo $_obj_data_sub_visit->get_name_l().', '.$_obj_data_sub_visit->get_name_f(); ?></a>   
                                    	<input type="hidden"
                                            name 	= "sub_visit_by[]"
                                            id		= "sub_visit_by_<?php echo $_obj_data_sub_visit->get_id_key(); ?>"
                                            value	="<?php echo $_obj_data_sub_visit->get_visit_by(); ?>" />                                     
                                        
                                    </td>
                                    
                                    <td><?php $visit_time = NULL;
										if($_obj_data_sub_visit->get_time_recorded()) 
										{
											$visit_time = date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_sub_visit->get_time_recorded()->getTimestamp());                                        }
                                        ?> 
                                        <?php echo $visit_time; ?>   
                                        <input 	type="hidden"                                                        	 
                                            name	="sub_visit_time_recorded[]" 
                                            id		="sub_visit_time_recorded_<?php echo $_obj_data_sub_visit->get_id_key(); ?>" 
                                            value 	= "<?php echo $visit_time; ?>">
                                    </td>
                                                                                  
                                    <td style="width:1px">													
                                        <input 
                                            type	="hidden" 
                                            name	="sub_visit_id[]" 
                                            id		="sub_visit_id_<?php echo $_obj_data_sub_visit->get_id_key(); ?>" 
                                            value	="<?php echo $_obj_data_sub_visit->get_id_key(); ?>" />
                                        <button 
                                            type	="button" 
                                            class 	="btn btn-danger btn-sm pull-right" 
                                            name	="sub_visit_row_del" 
                                            id		="sub_visit_row_del_<?php echo $_obj_data_sub_visit->get_id_key(); ?>" 
                                            onclick="deleteRow_sub_visit(this)"><span class="glyphicon glyphicon-minus"></span></button>        
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
                    onclick	="insRow_visit()">
                    <span class="glyphicon glyphicon-plus"></span></button>
            </fieldset>
        </div><!--/fg_audits-->
 
 <script src="../../libraries/javascript/options_update.js"></script>
 
 <script>
 
 	$(document).ready(function(event) {		
				
				
				options_update(event, null, '#facility');
					
			});
 
 	// Room search and add.
	$('.facility_filter').change(function(event)
	{
		options_update(event, null, '#facility');	
	});
 
 	// Room search and add.
	$('.room_search').change(function(event)
	{
		options_update(event, null, '#area');	
	});
  
	$(".building_search").click(function(event){
			
		// Need to populate the model with building drop down.
		//options_update(event, null, '#facility');
		
		//options_update(event, null, '#building_code');
		options_update(event, null, '#area');
		
		$(".modal_building_search").modal();
	});
	 
	
	
	// Room search and add.
	$('.account_filter').change(function(event)
	{
		options_update(event, null, '#account_search');	
	});
	
	$('.party_insert').click((function() {
		$('#sub_party_party_'+$party_id).val($('.account_search').val());
	
	})); 
	 
	var $party_id = 0;
	 
	function run_party_search($target_id){
		
		$party_id = $target_id;
		// Need to populate the model with building drop down.
		//options_update(event, null, '#facility');
		
		//options_update(event, null, '#building_code');
		//options_update(event, null, '#area');
		
		$(".modal_party_search").modal();
	}
	 
	 // Party add/remove.
 	var $temp_id_party = 0;	// Temp id for new party rows.
 
 	// Remove a party row.
 	function deleteRow_sub_party(row)
	{
		var i=row.parentNode.parentNode.rowIndex;
		document.getElementById('tbl_sub_party').deleteRow(i);
	}
 
 	// Inserts a new party row.
	function insRow_party()
	{			
		$('.parties').append(
			'<tr>'
				+'<td>'
					+'<select '
						+'name 	= "sub_party_party[]" '
						+'id	= "sub_party_party_'+$temp_id_party+'" '
						+'class	= "form-control">'
						+'<option value="" selected>Select Party</option> '
						<?php																
						if(is_object($_obj_field_source_party_list) === TRUE)
						{        
							// Generate table row for each item in list.
							for($_obj_field_source_party_list->rewind();	$_obj_field_source_party_list->valid(); $_obj_field_source_party_list->next())
							{	                                                               
								$_obj_field_source_party = $_obj_field_source_party_list->current();
																								
								$sub_party_label		= $_obj_field_source_party->get_name_l().', '.$_obj_field_source_party->get_name_f();
								
								// Add middle name if available
								if($_obj_field_source_party->get_name_m())
								{
									$sub_party_label .= ' '.$_obj_field_source_party->get_name_m();
								}
								
								$sub_party_label = htmlspecialchars($sub_party_label, ENT_QUOTES);
								
								?>
								+'<option value="<?php echo $_obj_field_source_party->get_id(); ?>"><?php echo $sub_party_label; ?></option>'
								<?php                                
							}
						}
					?>
					+'</select>'												
				+'</td>'  
				
				+'<td style="width:1px">'
					+'<button ' 
						+'type	="button" ' 
						+'class ="btn btn-info btn-sm pull-right" ' 
						+'name	="party_search" ' 
						+'id	="party_search_'+$temp_id_party+'" ' 
						+'onclick="run_party_search('+$temp_id_party+')"><span class="glyphicon glyphicon-search"></span></button>'
				+'</td>'
			
				+'<td style="width:1px">'													
					+'<input ' 
						+'type	="hidden" ' 
						+'name	="sub_party_id[]" ' 
						+'id	="sub_party_id_'+$temp_id_party+'" ' 
						+'value	="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
						
					+'<button ' 
						+'type	="button" ' 
						+'class ="btn btn-danger btn-sm pull-right" ' 
						+'name	="sub_party_row_del" ' 
						+'id	="sub_party_row_del_'+$temp_id_party+'" ' 
						+'onclick="deleteRow_sub_party(this)"><span class="glyphicon glyphicon-minus"></span></button>'        
				+'</td>'
			+'</tr>'
		
		);
			
			$temp_id_party--;
	}
	
	// Visit add/remove
	
	var $temp_id_visit = 0;
	
	function deleteRow_sub_visit(row)
	{
		var i=row.parentNode.parentNode.rowIndex;
		document.getElementById('tbl_sub_visit').deleteRow(i);
	}
	
	function insRow_visit()
	{			
		$('.visit').append(
			'<tr>'
				+'<td>'
					+'<select '
						+'name 	= "sub_visit_type[]" '
						+'id	= "sub_visit_type_'+$temp_id_visit+'" '
						+'class	= "form-control"> '
						+'<option value="" selected>Select Type</option> '							
						<?php
							if(is_object($_obj_data_list_event_type_list) === TRUE)
							{        
								// Generate table row for each item in list.
								for($_obj_data_list_event_type_list->rewind();	$_obj_data_list_event_type_list->valid(); $_obj_data_list_event_type_list->next())
								{	                                                               
									$_obj_data_list_event_type = $_obj_data_list_event_type_list->current();
									
									?>
									+'<option value="<?php echo $_obj_data_list_event_type->get_id(); ?>" ><?php echo $_obj_data_list_event_type->get_label(); ?></option>'
									<?php                                
								}
							}
						?>
						
					+'</select>'						
				+'</td>'  
				
				+'<td>'			                                          
					+'<select '
						+'name 	= "sub_visit_by[]" '
						+'id	= "sub_visit_by_'+$temp_id_visit+'" '
						+'class	= "form-control">'							
						<?php							
						
						// Set up account info.
						$access_obj = new \dc\stoeckl\status();
											
						if(is_object($_obj_field_source_account_list) === TRUE)
						{        
							// Generate table row for each item in list.
							for($_obj_field_source_account_list->rewind();	$_obj_field_source_account_list->valid(); $_obj_field_source_account_list->next())
							{	                                                               
								$_obj_field_source_account = $_obj_field_source_account_list->current();
								
								$sub_account_value 		= $_obj_field_source_account->get_id();																
								$sub_account_label		= $_obj_field_source_account->get_name_l().', '.$_obj_field_source_account->get_name_f();
								$sub_account_selected 	= NULL;
								
								if($_obj_field_source_account->get_account() == $access_obj->get_account())
								{
									$sub_account_selected = ' selected ';
								}									
								
								?>
								+'<option value="<?php echo $sub_account_value; ?>" <?php echo $sub_account_selected ?>><?php echo $sub_account_label; ?></option>'
								<?php                                
							}
						}
					?>
					+'</select>'
				+'</td>'
				
				+'<td>'                                                    	
					+'<input 	type="text" '                                                        	 
						+'name	= "sub_visit_time_recorded[]" ' 
						+'id	= "sub_visit_time_recorded_'+$temp_id_visit+'" ' 
						+'class	= "form-control" '
						+'value = "<?php echo date(APPLICATION_SETTINGS::TIME_FORMAT); ?>">'
				+'</td>'
															  
				+'<td style="width:1px">'													
					+'<input ' 
						+'type	="hidden" ' 
						+'name	="sub_visit_id[]" ' 
						+'id	="sub_visit_id_'+$temp_id_visit+'" ' 
						+'value	="<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
				
					+'<button ' 
						+'type	="button" ' 
						+'class ="btn btn-danger btn-sm pull-right" ' 
						+'name	="sub_visit_row_del" ' 
						+'id	="sub_visit_row_del_'+$temp_id_visit+'" ' 
						+'onclick="deleteRow_sub_visit(this)"><span class="glyphicon glyphicon-minus"></span></button>'        
				+'</td>'
			+'</tr>'
		
		);
		
		$temp_id_visit--;		
		
	}
	
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
                                +'<label class="control-label col-sm-1" for="sub_detail_category_'+$temp_finding+'" title="Category Filter: Choose an item to filter the available selections in Correction List by category."><span class="glyphicon glyphicon-filter"></span></label> '
                                +'<div class="col-sm-11">'
                                                                                            
                                    +'<select '
                                        +'name 		= "sub_detail_category[]" '
                                        +'id		= "sub_detail_category_'+$temp_finding+'" '
                                        +'class		= "form-control" '
                                        +'onChange 	= "update_corrections(this)"> '
                                        +'<?php echo $category_list_options; ?> '
                                    +'</select>'
                                +'</div>'
                            +'</div>'
                        
                            +'<div class="form-group"> '
                                +'<label class="control-label col-sm-1" for="sub_detail_correction_'+$temp_finding+'" title="Correction: Choose the nessesary correction here."><span class="glyphicon glyphicon-wrench"></span></label> '
                                +'<div class="col-sm-11">'								
                                    
                                    +'<select '
                                        +'name 	= "sub_detail_correction[]" '
                                        +'id	= "sub_detail_correction_'+$temp_finding+'" '
                                        +'class	= "form-control update_source_sub_detail_category_'+$temp_finding+'"> '                                        
                                        +'<?php echo $correction_list_options; ?>'                                        
                                    +'</select>'
                                +'</div>'
                            +'</div>'                                                        
                            
                            +'<div class="form-group" id="div_sub_detail_details_'+$temp_finding+'">'
                                +'<label class="control-label col-sm-1" for="sub_detail_details_'+$temp_finding+'" title="Comments: Add any specific comments or notes here."><span class="glyphicon glyphicon-list-alt"></span></label> '
                                +'<div class="col-sm-11">'
                                    +'<textarea ' 
                                        +'class	= "form-control" ' 
                                        +'rows 	= "5" ' 
                                        +'name	= "sub_detail_details[]" ' 
                                        +'id	= "sub_detail_details_'+$temp_finding+'"></textarea>'
                                +'</div>'
                            +'</div>'
                        
							+'<div class="form-group"> '	
								+'<label class="control-label col-sm-1" for="sub_detail_complete_'+$temp_finding+'" title="Complete: Select to indicate this particular correction has been rectified."><span class="glyphicon glyphicon-ok"></span></label>'								
								+ '<div class="col-sm-11"> '
									+ '<label class="radio-inline"><input type="radio" '
										+ 'class	= "sub_detail_complete_'+$temp_finding+'" '
										+ 'name	= "sub_detail_complete_'+$temp_finding+'" '
										+ 'id		= "sub_detail_complete_'+$temp_finding+'_1" '
										+ 'value	= "1" '
										+ 'required><span class="glyphicon glyphicon-thumbs-up text-success" style="font-size:large;"></span></label> '
									+ '&nbsp;'
									+ '<label class="radio-inline"><input type	= "radio" '
										+ 'class	= "sub_detail_complete_'+$temp_finding+'" '
										+ 'name	= "sub_detail_complete_'+$temp_finding+'" '
										+ 'id		= "sub_detail_complete_'+$temp_finding+'_0" '
										+ 'value	= "0" '
										+ 'required checked><span class="glyphicon glyphicon-thumbs-down text-danger" style="font-size:large;"></span></label>'
								+ '</div>'
							+ '</div>'
					
						+'</td>'
                        
                        +'<td>'
							+'<input ' 
                                +'type	= "hidden" ' 
                                +'name	= "sub_detail_id[]" ' 
                                +'id	= "sub_detail_id_'+$temp_finding+'" ' 
                                +'value	= "<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>" />'
								
                            +'<button ' 
                                +'type	= "button" ' 
                                +'class = "btn btn-danger btn-sm" ' 
                                +'name	= "sub_detail_row_del" ' 
                                +'id	= "sub_detail_row_del_'+$temp_finding+'" ' 
                                +'onclick = "deleteRow_sub_finding(this)"><span class="glyphicon glyphicon-minus"></span></button>'       
                        +'</td>'
                    +'</tr>'			
                );
                
                tinymce.init({
                selector: '#sub_detail_details_'+$temp_finding,
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
				var $category = null;
		
				           
                // Get element by css seelctor - this returns a list.
                var $target = document.querySelectorAll($update_select_id);
        
                // Iterate list and update al elements (In most cases, there will
                // only be one).
                for (var i = 0; i < $($target).length; i++) 
                {
                    $($target).attr('disabled', false);
                    
					$($target).load('<?php echo APPLICATION_SETTINGS::DIRECTORY_PRIME; ?>/inspection_saa_corrections_list.php?category=' + $($val).val() + '&inclusion=<?php echo $inspection_type; ?>');
                }			
            }
</script>
<!--/Include: <?php echo __FILE__; ?>-->