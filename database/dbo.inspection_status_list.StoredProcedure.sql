USE [inspection]
GO

/****** Object:  StoredProcedure [dbo].[inspection_status_list]    Script Date: 2017-07-19 17:10:53 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


CREATE PROCEDURE [dbo].[inspection_status_list]
	
	-- Parameters
	
	-- paging
	@param_page_current			int				= 1,
	@param_page_rows			int				= 10,	

	-- Sorting
	@param_sort_field			tinyint			= 3,
	@param_sort_order			bit				= 0,

	-- filters
	@param_date_start			datetime2		= NULL,
	@param_date_end				datetime2		= NULL
	
AS	
	SET NOCOUNT ON;
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
			SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.								
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 0	THEN _main.label			END ASC,
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 1	THEN _main.label			END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _master.create_time	END ASC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _master.create_time	END DESC,
								CASE WHEN @param_sort_field = 3 AND @param_sort_order = 0	THEN _main.sort_order		END ASC,
								CASE WHEN @param_sort_field = 3 AND @param_sort_order = 1	THEN _main.sort_order		END DESC) AS id_row_local,
				_master.id, 
				_master.id_key,
				_main.label,
				_main.details,
				_main.sort_order,
				_master.create_time
			INTO #cache_primary
			FROM dbo.tbl_inspection_status_source _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 
			WHERE _master.active = 1 AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL)

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows
	
	

GO


