<!--Include: <?php echo __FILE__ . ", Last update: " . date(DATE_ATOM,filemtime(__FILE__)); ?>-->

    <!-- Party search modal -->
    <div id="party_search" class="modal fade modal_party_search" role="dialog">
        <div class="modal-dialog">                
        <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Party Account Search</h4>
                    
                    <p>Type a few letters from a party's name into the <span class="text-info">filter</span> field to filter available party account choices.</p>
                </div>
                
                <?php
                
                    // Populate the facility select.
                    //
                    // If the current record has a building/room selected, then
                    // we will populate Facility Select with that value.
                    // Otherwise we'll check to see if the "last selected"
                    // variable has been populated, and if so use it.
                    //
                    // If none of these can be found, then there is no default
                    // value to be had.
                    //if($_obj_data_sub_area_list->get_building_code())
                    //{			
                    //    $building_selection = $_obj_data_sub_area_list->get_building_code();
                    //}
                    //else
                    //{
                        // Verify the last selected session var exisits, and
                        // if it does, use it as our selected value.
                    //    if(isset($_SESSION[SESSION_ID::LAST_BUILDING]) == TRUE)
                    //    {
                    //        $building_selection = $_SESSION[SESSION_ID::LAST_BUILDING];
                    //    }
                    //}
                ?>
                                     
                <div class="modal-body"> 
                    
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="account_filter">Filter</label>
                        <div class="col-sm-10">
                            <input name="account_filter" 
                                list="browsers"
                                id="account_filter" 
                                data-current=""                            
                                class="account_filter form-control">
                        </div>
                    </div>
                                                     
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="account_search">Account</label>
                        <div class="col-sm-10">
                            <select name="account_search" 
                                id="account_search" 
                                data-current="<?php echo $building_selection; ?>" 
                                data-source-url="account_options.php" 
                                data-extra-options='<option value="">Select Account</option>'
                                data-grouped="1"
                                class="account_search form-control">
                                    <!--This option is for valid HTML5; it is overwritten on load.--> 
                                    <option value="0">Select Account</option>                                    
                                    <!--Options will be populated on load via jquery.-->                                 
                            </select>
                        </div>
                    </div>
                    
                    <br />
                    <br />                           
                </div><!--Model body-->
                
                <div class="modal-footer">
                    <button type="button" class="party_insert btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-save"></span> Insert</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>                
        </div>
    </div><!-- #party_search -->
<!--/Include: <?php echo __FILE__; ?>-->