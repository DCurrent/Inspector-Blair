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
			require __DIR__.'/model_location.php'; 
			
			// List queries
			// --Status
			
			// Set up database.
			$form_common_query = new \dc\yukon\Database();			
			
			// --Accounts (Inspector)
			$_obj_field_source_account_list = new \data\Account();
		
			$form_common_query->set_sql('{call account_list_inspector()}');
			$form_common_query->query();
			
			$form_common_query->get_line_params()->set_class_name('\data\Account');
			
			$_obj_field_source_account_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_field_source_account_list = $form_common_query->get_line_object_list();	
			
			// --Accounts (Party)
			$_obj_field_source_party_list = new \data\Account();
		
			$form_common_query->set_sql('{call account_list_party()}');
			$form_common_query->query();
			
			$query->get_line_params()->set_class_name('\data\Account');
			
			$_obj_field_source_party_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_field_source_party_list = $form_common_query->get_line_object_list();		
			
			// --Event type
			$_obj_data_list_event_type_list = new \data\Common();
		
			$form_common_query->set_sql('{call inspection_status_list()}');
			$form_common_query->query();
			
			$form_common_query->get_line_params()->set_class_name('\data\Common');
			
			$_obj_data_list_event_type_list = new SplDoublyLinkedList();
			if($form_common_query->get_row_exists() === TRUE) $_obj_data_list_event_type_list = $form_common_query->get_line_object_list();
				
			?>
        
        
        
        <!-- Location display. -->
        <?php 
        
            $building_code_display = NULL;
            
            if($_main_data->get_building_code())
            {
                $building_code_display = trim($_main_data->get_room_id()).', '. $_main_data->get_building_code().' - '.$_main_data->get_building_name(); 
            }
        
        ?>        
        
        <div class="form-group">
            <label class="control-label col-sm-2" for="building">Location</label>
            <div class="col-sm-10">
                <p class="form-control-static"><a href = "area.php?id=<?php echo $_main_data->get_room_code();  ?>"
                    
                    data-toggle	= ""
                    title		= "View location detail."
                    target		= "_new" 
                    ><?php echo trim($building_code_display); ?></a></p>          
            </div>
        </div>
        
        <div class="form-group">  
        	<div class="col-sm-2">
            </div>          
            	<div class="col-sm-10">
                    <table class="table table-striped table-hover">
                        <!--caption>Location Details</caption-->
                        <thead>
                        </thead>
                        <tfoot>
                        </tfoot>
                        <tbody id="tbody_room_data" class="">
                            <tr>
                                <td>Biosafety Level</td>
                                <td>In Progress</td>
                            </tr>
                            
                            <tr>
                                <th>Chemical Lab Class</th>
                                <td>In Progress</td>
                            </tr>
                            
                            <tr>
                                <th>Chemical Operations Class</th>
                                <td>In Progress</td>
                            </tr>
                            
                            <tr>
                                <th>Department</th>
                                <td>In Progress</td>
                            </tr>
                            
                            <tr>
                                <th>Hazardous Waste</th>
                                <td>In Progress</td>
                            </tr>
                            
                            <tr>
                                <th>Radiation Usage </th>
                                <td>In Progress</td>
                            </tr>
                            <tr>
                                <th>X-ray Usage </th>
                                <td>In Progress</td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        
        <!-- Room code entry -->                    
        <div class="form-group">
            <label class="control-label col-sm-2" for="room_code">Room Code</label>
            <div class="col-sm-9">
                <input type="text" class="form-control"  name="room_code" id="room_code" placeholder="Room code" value="<?php echo $_main_data->get_room_code(); ?>">
            </div>
            
            <div class="col-sm-1">
              <a href="#"
                    class		="btn btn-sm btn-info btn-responsive building_search pull-right" 
                    data-toggle	="modal"
                    title		="Find a room barcode."
                    
                    ><span class="glyphicon glyphicon-search"></span></a>
            </div>
        </div>
        
        <!-- Parties -->
        <div class="form-group">
       	  <div class="col-sm-2">
          </div>                       
          <fieldset class="col-sm-10" >
                <legend>Party Review</legend> 
                                                                  
                <table class="table table-striped table-hover" id="tbl_sub_party"> 
                    <thead>
                        <tr>
                            <th>Responsible Party</th>
                            <th><!--Party search button--></th>
                            <th><!--ID, Delete Button--></th>                            
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
                                if(!$_obj_data_sub_party->get_id()) $_obj_data_sub_party->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                
                            ?>
                                <tr>
                                    <td>     
                                        <!--Party: <?php echo $_obj_data_sub_party->get_item(); ?>-->
                                                                                                
                                        <select
                                            name 	= "sub_party_party[]"
                                            id		= "sub_party_party_<?php echo $_obj_data_sub_party->get_id(); ?>"
                                            class	= "form-control">
                                            <?php																
                                            if(is_object($_obj_field_source_party_list) === TRUE)
                                            {        
                                                // Generate table row for each item in list.
                                                for($_obj_field_source_party_list->rewind(); $_obj_field_source_party_list->valid(); $_obj_field_source_party_list->next())
                                                {	                                                               
                                                    $_obj_field_source_party = $_obj_field_source_party_list->current();
                                                    
                                                    $sub_party_value 		= $_obj_field_source_party->get_id();																
                                                    $sub_party_label		= $_obj_field_source_party->get_name_l().', '.$_obj_field_source_party->get_name_f();
                                                    
                                                    // Add middle name if available
                                                    if($_obj_field_source_party->get_name_m())
                                                    {
                                                        $sub_party_label .= ' '.$_obj_field_source_party->get_name_m();
                                                    }
                                                    
                                                    $sub_party_selected 	= NULL;
                                                            
                                                    if($_obj_data_sub_party->get_item())
                                                    {
                                                        if($_obj_data_sub_party->get_item() == $sub_party_value)
                                                        {
                                                            $sub_party_selected = ' selected ';
                                                        }								
                                                    }                                                                        
                                                    
                                                    // Now convert special characters.
                                                    $sub_party_label = htmlspecialchars($sub_party_label, ENT_QUOTES);
                                                    
                                                    ?>
                                                    <option value="<?php echo $sub_party_value; ?>" <?php echo $sub_party_selected ?>><?php echo $sub_party_label; ?></option>
                                                    <?php                                
                                                }
                                            }
                                        ?>
                                        </select>
                                    </td>
                                    <td style="width:1px">
                                    	<a href="#"
                                                class		="btn btn-sm btn-info btn-responsive party_search" 
                                                data-toggle	="modal"
                                                data-confirm_target_id="sub_party_party_<?php echo $_obj_data_sub_party->get_id(); ?>"
                                                title		="Find a party selection."                                                
                                                ><span class="glyphicon glyphicon-search"></span></a>
                                    </td>
                                    <td style="width:1px">                              													
                                        <input 
                                            type	="hidden" 
                                            name	="sub_party_id[]" 
                                            id		="sub_party_id_<?php echo $_obj_data_sub_party->get_id(); ?>" 
                                            value	="<?php echo $_obj_data_sub_party->get_id(); ?>" />
                                            
                                        <button 
                                            type	="button" 
                                            class 	="btn btn-danger btn-sm pull-right" 
                                            name	="sub_party_row_del" 
                                            id		="sub_party_row_del_<?php echo $_obj_data_sub_party->get_id(); ?>" 
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

		<div class="form-group">       
       	  <div class="col-sm-2">
            </div>                
          <fieldset class="col-sm-10">
                <legend>Audits</legend>
                                                
                <table class="table table-striped table-hover" id="tbl_sub_visit"> 
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
                                if(!$_obj_data_sub_visit->get_id()) $_obj_data_sub_visit->set_id(\dc\yukon\DEFAULTS::NEW_ID);
                                
                            ?>
                                <tr>
                                    <td>
                                        <!--Visit Type: <?php echo $_obj_data_sub_visit->get_visit_type(); ?>-->
                                        <select
                                            name 	= "sub_visit_type[]"
                                            id		= "sub_visit_type_<?php echo $_obj_data_sub_visit->get_id(); ?>"
                                            class	= "form-control">
                                            <?php
                                            if(is_object($_obj_data_list_event_type_list) === TRUE)
                                            {        
                                                // Generate table row for each item in list.
                                                for($_obj_data_list_event_type_list->rewind();	$_obj_data_list_event_type_list->valid(); $_obj_data_list_event_type_list->next())
                                                {	                                                               
                                                    $_obj_data_list_event_type = $_obj_data_list_event_type_list->current();
                                                   
                                                    $sub_visit_type_selected = NULL;         
                                                   
                                                    if($_obj_data_sub_visit->get_visit_type() == $_obj_data_list_event_type->get_id())
                                                    {
                                                        $sub_visit_type_selected = ' selected ';
                                                    }								
                                                    
                                                    
                                                    ?>
                                                    <option value="<?php echo $_obj_data_list_event_type->get_id(); ?>" <?php echo $sub_visit_type_selected ?>><?php echo $_obj_data_list_event_type->get_label(); ?></option>
                                                    <?php                                
                                                }
                                            }
                                        ?>
                                        </select>
                                        
                                    </td>  
                                    
                                    <td>     
                                        <!--Visit By: <?php echo $_obj_data_sub_visit->get_visit_by(); ?>-->                                           
                                        <select
                                            name 	= "sub_visit_by[]"
                                            id		= "sub_visit_by_<?php echo $_obj_data_sub_visit->get_id(); ?>"
                                            class	= "form-control">
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
                                                            
                                                    if($_obj_data_sub_visit->get_visit_by())
                                                    {
                                                        if($_obj_data_sub_visit->get_visit_by() == $sub_account_value)
                                                        {
                                                            $sub_account_selected = ' selected ';
                                                        }								
                                                    }
                                                    else
                                                    {
                                                        if($_obj_field_source_account->get_account() == $access_obj->get_account())
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
                                        </select>
                                    </td>
                                    
                                    <td>                                                    	
                                        <input 	type="text"                                                        	 
                                            name	="sub_visit_time_recorded[]" 
                                            id		="sub_visit_time_recorded_<?php echo $_obj_data_sub_visit->get_id(); ?>" 
                                            class	="form-control"
                                            value 	= "<?php if($_obj_data_sub_visit->get_time_recorded()) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_sub_visit->get_time_recorded()->getTimestamp()); ?>">
                                    </td>
                                                                                  
                                    <td style="width:1px">													
                                        <input 
                                            type	="hidden" 
                                            name	="sub_visit_id[]" 
                                            id		="sub_visit_id_<?php echo $_obj_data_sub_visit->get_id(); ?>" 
                                            value	="<?php echo $_obj_data_sub_visit->get_id(); ?>" />
                                        <button 
                                            type	="button" 
                                            class 	="btn btn-danger btn-sm pull-right" 
                                            name	="sub_visit_row_del" 
                                            id		="sub_visit_row_del_<?php echo $_obj_data_sub_visit->get_id(); ?>" 
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
        </div>
                                        
        <!--<div class="form-group">
            <label class="control-label col-sm-2" for="name">Label:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control"  name="label" id="label" placeholder="Inspection Title" value="<?php echo $_main_data->get_label(); ?>">
            </div>
        </div>-->
 
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
		
	$('.room_code_insert').click((function() {
	
		$('input[name="room_code"]').val($('.room_code_search').val());
	
	}));
 
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
					+'<a href="#" '
							+'class			="btn btn-sm btn-info btn-responsive party_search" '
							+'data-toggle	="modal" '
							+'data-confirm_target_id="sub_party_party_'+$temp_id_party+'" '
							+'title			="Find a party selection." '                                               
							+'><span class="glyphicon glyphicon-search"></span></a>'
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
						$access_obj = new \dc\access\status();
											
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
</script>
<!--/Include: <?php echo __FILE__; ?>-->