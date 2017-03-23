<!--Include: <?php echo __FILE__ . ", Last update: " . date(DATE_ATOM,filemtime(__FILE__)); ?>-->

    <!-- Party code search modal -->
    <div id="party_search" class="modal fade modal_party_search" role="dialog">
        <div class="modal-dialog">                
        <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Party Search</h4>
                    
                    <p>Select a building, then choose a room. When you are finished, press Insert to populate the Room Code field with your room selection. If needed, type a few letters from a facility name into the <span class="text-info">filter</span> field to filter available facility selections.</p>
                </div>
                
                <?php
                
                    // Default selection code goes here.
                ?>
                                     
                <div class="modal-body"> 
                    
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="party_filter">Filter</label>
                        <div class="col-sm-10">
                            <input name="party_filter" 
                                list="browsers"
                                id="party_filter" 
                                data-current=""                            
                                class="model_party_filter form-control">
                        </div>
                    </div>
                                                     
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="party">Party</label>
                        <div class="col-sm-10">
                            <select name="model_party" 
                                id="model_party" 
                                data-current="<?php echo //$building_selection; ?>" 
                                data-source-url="insert_party.php" 
                                data-extra-options='<option value="">Select Party</option>'
                                data-grouped="1"
                                class="form-control">
                                    <!--This option is for valid HTML5; it is overwritten on load.--> 
                                    <option value="0">Select Party</option>                                    
                                    <!--Options will be populated on load via jquery.-->                                 
                            </select>
                        </div>
                    </div> 
                    
                    <br />
                    <br />                           
                </div><!--Model body-->
                
                <div class="modal-footer">
                    <button type="button" class="party_insert btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-save"></span> Confirm</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>                
        </div>
    </div>
    
    <script>
		$('#modal_party_search').on('show.bs.modal', function (event) {
		  var button = $(event.relatedTarget) // Button that triggered the modal
		  var recipient = button.data('confirm_target_id') // Extract info from data-* attributes
		  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
		  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		  var modal = $(this)
		  modal.find('.modal-title').text('New message to ' + recipient)
		  modal.find('.modal-body input').val(recipient)
		})
	</script>
<!--/Include: <?php echo __FILE__; ?>-->