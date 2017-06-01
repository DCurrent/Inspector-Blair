<?php

	namespace dc\recordnav;
	
	require_once('config.php');

	// Paging handler.
	interface iPaging
	{
		// Accessors
		function get_markup();
		function get_page_current();
		function get_page_last();
		function get_row_count();
		function get_row_max();	
		
		// Mutators
		function set_markup($value);		
		function set_row_count_total($value);			
		function set_row_max($value);
		function set_page($value);
		function set_page_last($value);
		
		// Operations.
		function generate_paging_markup();
		function populate_from_request();				
	}
	
	class Paging implements iPaging
	{
		const 
			ROW_MAX					= 25,
			REQUEST_KEY_PAGE_NUMBER	= 'page'; // If this is changed, make sure to change the member name and mutator.
					
		private			
			$markup				= NULL,		
			$page_last			= NULL,			
			$page_current		= NULL,
			$row_count_total	= NULL,	// Row count without paging.
			$row_max			= NULL;
				
		public function __construct()
		{			
			$this->populate_from_request();
			
			$this->row_max = self::ROW_MAX;
			
			// Make sure the page number request is a positive numeric value.
			if (!is_numeric($this->page_current) || $this->page_current < 1)
			{ 
				$this->page_current = 1; 
			}						
		}				
		
		// Accessors
		public function get_markup()
		{
			return $this->markup;
		}
		
		public function get_row_max()
		{
			return $this->row_max;
		}
		
		public function get_row_count()
		{
			return $this->row_count_total;
		}
		
		public function get_page_last()
		{
			return $this->page_last;
		}	
		
		public function get_page_current()
		{
			return $this->page_current;
		}
		
		// Mutators
		public function set_markup($value)
		{
			$this->markup = $value;
		}
		
		public function set_row_count_total($value)
		{
			$this->row_count_total = $value;
		}
		
		public function set_row_max($value)
		{
			$this->row_max = $value;
		}
		
		public function set_page_last($value)
		{
			$this->page_last = $value;
		}
		
		public function set_page($value)
		{
			$this->page_current = $value;
		}
		
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
				
		public function generate_paging_markup()
		{			
			$i			= 0;
			$url_query	= new \dc\url\URLFix;
			$page_url	= NULL;
			$active 	= NULL;
		
			// Start caching page contents.
			ob_start();
			?>
			
			<ul class="pagination">
			
			<?php
			$max_buttons = 7;
			
			/* if the page number is < 3, render the page number
			// if the page number is within +-2 of the current page, render the page number
			// if the page number is > the total number of pages -3 render the page number
			// if the last page number wasn't rendered and dots haven't already been rendered render dots '...' do indicate a gap

			for($i=1; $i <= $this->page_last; $i++)
			{
				$url_query->set_data(self::REQUEST_KEY_PAGE_NUMBER, $i);				
				$page_url = $url_query->return_url_encoded();
				
				// Reset active css.
				$active = NULL;
					
				if($i == $this->page_current)
				{
					$active = 'active';
				}
				
				if($i < ($max_buttons / 2))
				{
				?>
					<li class = "<?php echo $active; ?>"><a href="<?php echo $page_url; ?>"><?php echo $i; ?></a></li>
				<?php
				}
				else if($i == round ($max_buttons / 2, 0))
				{
				?>
					<li class="disabled"><a href="<?php echo $page_url; ?>">...</a></li>
				<?php
				}
							
			}
			*/
				?>
			
			<?php
			
			// else do nothing
			for($i=1; $i <= $this->page_last; $i++)
			{
				// Build URL query.		
				$url_query->set_data(self::REQUEST_KEY_PAGE_NUMBER, $this->page_current);
				$page_url = $url_query->return_url_encoded();
				
				// Reset active css.
				$active = NULL;
					
				if($i == $this->page_current)
				{
					$active = 'active';
				}
				?>
					<li class = "<?php echo $active; ?>"><a href="<?php echo $page_url; ?>"><?php echo $i; ?></a></li>
				<?php	
				
			}
			
			?>
			</ul>
            
			<!--Record count inidicator-->
            <br /><span class="badge"><?php echo $this->row_count_total; ?>&nbsp;<?php echo ($this->row_count_total == 1 ? 'record' : 'records'); ?></span>
			
			<?php
			
			// Collect contents from cache and then clean it.
			$this->markup = ob_get_contents();
			ob_end_clean();
			
			return $this->markup;
		}
	}

?>