USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[audit_question_list]    Script Date: 6/14/2017 11:08:14 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[audit_question_list]
	
	-- Parameters
	
	-- paging
	@param_page_current			int				= 1,
	@param_page_rows			int				= 10,	

	-- Sorting
	@param_sort_field			tinyint			= 1,
	@param_sort_order			bit				= 0,

	-- filters
	@param_date_start			datetime2		= NULL,
	@param_date_end				datetime2		= NULL,
	@param_status				smallint		= NULL,
	@param_category				int				= NULL
	
AS	
	SET NOCOUNT ON;
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
			SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.								
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 0	THEN _main.finding			END ASC,
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 1	THEN _main.finding			END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _master.create_time	END ASC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _master.create_time	END DESC) AS id_row_local,
				_master.id, 
				_master.id_key,
				_main.label,
				_main.details,
				_main.corrective_action,
				_main.finding,
				_master.create_time,
				_main.status
			INTO #cache_primary
			FROM dbo.tbl_audit_question _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 
			WHERE _master.active = 1 
				-- Filter, Date
				AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL)
				-- Filter, Status
				AND (_main.status = @param_status OR @param_status = -1 OR @param_status IS NULL)

				-- Categories are stored in a sub table, so we'll need to look for their exisitence 
				-- in that table here.
				AND (Exists(
					SELECT 1
					FROM dbo.tbl_audit_question_category As _category
					JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _category.fk_id = _master.id 
						AND (_master.active = 1)
						AND ((_category.item IN (@param_category)) OR (@param_category IS NULL OR @param_category = -1))))

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows
	
	
