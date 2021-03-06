USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[master_version_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[master_version_list]
	
	-- paging
	@param_page_current	int				= 1,
	@param_page_rows	int				= 10,

	-- Main filter
	@param_id			int				= NULL,
	
	-- Sorting
	@sort_field			tinyint			= 1,
	@sort_order			bit				= 1,

	@param_date_start	datetime2		= NULL,
	@param_date_end		datetime2		= NULL

AS	
	SET NOCOUNT ON;		
		
	-- Combine account and master table so we can join by
	-- the group id below.
	SELECT _account.account,		 
		_account.department, 
		_account.details,
		_master.create_time, 
		_master.id,
		_account.id_key, 
		_account.label, 
		_account.name_f,
		_account.name_l,
		_account.name_m,
		_account.status,
		_master.update_time  
	INTO #cache_account FROM tbl_account _account JOIN tbl_master _master ON _master.id_key = _account.id_key 
	WHERE _master.active = 1

	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	(SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.
								CASE WHEN @sort_field = 1 AND @sort_order = 0	THEN _master.create_time	END ASC,
								CASE WHEN @sort_field = 1 AND @sort_order = 1	THEN _master.create_time	END DESC,
								CASE WHEN @sort_field = 2 AND @sort_order = 0	THEN _account.name_l + _account.name_f + _account.name_m	END ASC ,
								CASE WHEN @sort_field = 2 AND @sort_order = 1	THEN _account.name_l + _account.name_f + _account.name_m	END DESC) 
		AS _row_id,
			_master.id,
			_master.id_key, 
			_master.create_time, 
			_account.name_l,
			_account.name_m,
			_account.name_f,
			_account.account,
			_master.active
	INTO #cache_primary
	FROM tbl_master _master LEFT JOIN #cache_account _account ON _master.create_by = _account.id
	WHERE (_master.id = @param_id) AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL))

	
	-- Execute paging SP to output paged records and control data.
	EXEC master_paging
			@param_page_current,
			@param_page_rows
	
	
	

GO
