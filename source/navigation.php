<?php

	const NAV_INDENT = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

	class class_navigation
	{
		private
			$access_obj			= NULL,
			$directory_local	= NULL,
			$directory_prime	= NULL,
			$markup_nav			= NULL,
			$markup_footer		= NULL,
			$record_nav			= NULL;
		
		public function __construct()
		{
			$this->directory_prime 	= APPLICATION_SETTINGS::DIRECTORY_PRIME;
			$this->access_obj		= new \dc\stoeckl\status();
			
			$this->access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
			
			// Really just to get the autoloader going so we
			// can use the config settings.
			$this->record_nav = new \dc\recordnav\RecordNav();
		}
		
		public function get_directory_local()
		{
			return $this->directory_local;
		}
		
		public function get_directory_prime()
		{
			return $this->get_directory_prime();
		}
		
		public function set_directory_local($value)
		{
			$this->directory_local = $value;
		}
		
		public function get_markup_footer()
		{
			return $this->markup_footer;
		}
		
		public function get_markup_nav()
		{
			return $this->markup_nav;
		}
			
		public function generate_markup_nav()
		{
			$class_add = NULL;
			
			if(!$this->access_obj->get_account()) $class_add .= "disabled hidden";
			
			// Start output caching.
			ob_start();
		?>
        	 <nav id="main_nav" class="navbar navbar-default">
                <div id="main_nav_container" class="container-fluid">
                    <div id="main_nav_header" class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav_main">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>                        
                        </button>
                        <a class="navbar-brand" href="<?php echo $this->directory_prime; ?>"><?php echo APPLICATION_SETTINGS::NAME; ?></a>
                    </div><!--#main_nav_header-->
                    
                    <div class="collapse navbar-collapse" id="nav_main">
                        <ul class="nav navbar-nav">
                            <!--<li class="active"><a href="#">Home</a></li>-->
                            <li class="dropdown">
                                <a class="dropdown-toggle <?php echo $class_add; ?>" data-toggle="dropdown" href="#">Inspection<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $this->directory_prime; ?>?id_form=1550&amp;list=1">Inspection List</a></li>
                                    <!--<li><a href="<?php echo $this->directory_prime; ?>/inspection_autoclave.php&#63;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>"><span class="glyphicon glyphicon-plus"></span> Autoclave</a></li>-->
                                    <li><a href="<?php echo $this->directory_prime; ?>?id_form=1550&amp;nav_command=<?php echo \dc\recordnav\COMMANDS::NEW_BLANK;?>&amp;id=<?php echo \dc\yukon\DEFAULTS::NEW_ID; ?>"><span class="glyphicon glyphicon-plus"></span> SAA</a></li>
                                </ul>
                            </li>
                           <li><a class="disabled <?php echo $class_add; ?>" href="#">Review</a></li>
                            <li class="dropdown">
                                <a class="dropdown-toggle <?php echo $class_add; ?>" data-toggle="dropdown" href="#">System<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                	<li class="dropdown-header">Administration</li>
                                        <li><a href="<?php echo $this->directory_prime; ?>/config_common_entry_list.php"><?php echo NAV_INDENT; ?>Common Entry Forms</a></li>
                                    	<li><a href="<?php echo $this->directory_prime; ?>?id_form=1256&amp;list=1"><?php echo NAV_INDENT; ?>Accounts</a></li>
                                    	<li><a href="<?php echo $this->directory_prime; ?>?id_form=1182&amp;list=1"><?php echo NAV_INDENT; ?>Account Roles</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>/audit_type_list.php?id_form=1599&amp;list=1"><?php echo NAV_INDENT; ?>Visit Types</a></li>
                                    <li class="divider"></li>
                                	<li class="dropdown-header">Area/Rooms</li>
                                    	<li><a href="<?php echo $this->directory_prime; ?>/area_list.php"><?php echo NAV_INDENT; ?>Areas</a></li>
                                    	<li><a href="<?php echo $this->directory_prime; ?>?id_form=1185&amp;list=1"><?php echo NAV_INDENT; ?>Area Types</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1169&amp;list=1"><?php echo NAV_INDENT; ?>Biosafety Levels</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1170&amp;list=1"><?php echo NAV_INDENT; ?>Chemical Lab Classes</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1171&amp;list=1"><?php echo NAV_INDENT; ?>Chemical Operations Classes</a></li>	
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1608&amp;list=1"><?php echo NAV_INDENT; ?>NFPA 45 Lab Unit Hazard Classification</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">Findings</li>                                    
                                    	<li><a href="<?php echo $this->directory_prime; ?>/?id_form=1548&amp;list=1"><?php echo NAV_INDENT; ?>Categories</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>/?id_form=1552&amp;list=1"><?php echo NAV_INDENT; ?>Questions</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>/?id_form=1172&amp;list=1"><?php echo NAV_INDENT; ?>Ratings</a></li>                                 	
                                    <li class="divider"></li>
                                    <li class="dropdown-header">Biological Agents</li>
                                        <li><a href="<?php echo $this->directory_prime; ?>/biological_agent_list.php"><?php echo NAV_INDENT; ?>Agents</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1168&amp;list=1"><?php echo NAV_INDENT; ?>Risk Groups</a></li>
                                        <li><a href="<?php echo $this->directory_prime; ?>?id_form=1148&amp;list=1"><?php echo NAV_INDENT; ?>Hosts</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                        <?php
							if($this->access_obj->get_account())
							{
						?>
                        		<li><a href="<?php echo $this->access_obj->get_config()->get_authenticate_url(); ?>?access_action=<?php echo \dc\stoeckl\ACTION::LOGOFF; ?>"><span class="glyphicon glyphicon-log-out"></span> <?php echo $this->access_obj->name_full(); ?></a></li>
                        <?php
							}
							else
							{
						?>
                        		<li><a href="<?php echo $this->access_obj->get_config()->get_authenticate_url(); ?>"><span class="glyphicon glyphicon-log-in"></span> Guest</a></li>
                        <?php
							}
						?>                   
                        </ul>
                                    
                    </div><!--#nav_main-->
                </div><!--#main_nav_container-->
            </nav><!--#main_nav--> 
        
                 	
        <?php
			
			// Collect contents from cache and then clean it.
			$this->markup_nav = ob_get_contents();
			ob_end_clean();	
			
			return $this->markup_nav;
		}			
		
		public function generate_markup_footer()
		{
			// Start output caching.
			ob_start();
		?>
        	
            <div id="nav_footer" class="container well" style="width:95%; margin-top:20px;">
            	<a href="//www.uky.edu"><img src="<?php echo $this->directory_prime; ?>/media/uk_logo.png" alt="University of Kentucky" class="img-responsive" style="float:left; margin-top:10px; margin-bottom:5px;"></a>
                            
                <ul class="list-inline">                       
                    <li>
                    	<ul class="list-unstyled text-muted small" style="margin-bottom:10px;">
                        	<li><?php echo APPLICATION_SETTINGS::NAME; ?> Ver <?php echo APPLICATION_SETTINGS::VERSION; ?>, Engine <?php echo PHP_VERSION; ?></li>   
                        	<li>Developed by: <a href="mailto:dvcask2@uky.edu"><span class="glyphicon glyphicon-envelope"></span> Caskey, Damon V.</a></li>
                            <li>Copyright &copy; <?php echo date("Y"); ?>, University of Kentucky</li>
                            <li>Last update: 
                                <?php 
                                echo date(APPLICATION_SETTINGS::TIME_FORMAT, filemtime($_SERVER['SCRIPT_FILENAME']));  
                                
                                if (isset($iReqTime)) 
                                { 
                                    echo ". Generated in " .round(microtime(true) - $iReqTime,3). " seconds."; 
                                } 
                                ?></li>
                     	</ul>
                     </li>
                     <div style="float:right;">
                        <img src="<?php echo $this->directory_prime; ?>/media/php_logo_1.png" class="img-responsive pull-right" alt="Powered by objected oriented PHP." title="Powered by object oriented PHP." />
                     </div>
                </ul>
            </div><!--#nav_footer-->
        <?php
			// Collect contents from cache and then clean it.
			$this->markup_footer = ob_get_contents();
			ob_end_clean();
			
			return $this->markup_footer;
		}
	}

?>