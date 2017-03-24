USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_role_list]    Script Date: 2017-02-07 08:45:00 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


ALTER PROCEDURE [dbo].[account_role_list]
	
	-- Parameters
	
	-- paging
	@param_page_current		int				= 1,
	@param_page_rows		int				= 10
	
	-- Sorting
	
	-- Filters	
	
AS	
	SET NOCOUNT ON;
		
	-- Create and Populate main table var. This is the primary query. Order
	-- and query details go here.
		SELECT
				_master.id, 
				_master.id_key,
				_master.create_time,
				_master.active,
				_main.label, 
				_main.details				
		INTO #cache_primary
		FROM dbo.tbl_account_role_source AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key 
		WHERE _master.active = 1
		ORDER BY _main.label, _master.create_time
	
	-- Execute paging SP to output paged records and control data.
	EXEC master_paging
			@param_page_current,
			@param_page_rows
	
	
