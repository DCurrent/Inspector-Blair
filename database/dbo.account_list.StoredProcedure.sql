USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_list]    Script Date: 2017-07-24 18:21:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

ALTER PROCEDURE [dbo].[account_list]
	
	-- Parameters
	
	-- paging
	@page_current		int				= 1,
	@page_rows			int				= 10,	

	-- Sorting
	@sort_field			tinyint			= 2,
	@sort_order			bit				= 0,

	-- Filter
	@name_like			varchar(25)		= NULL
	
AS	
	SET NOCOUNT ON;
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
			SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.
								CASE WHEN @sort_field = 1 AND @sort_order = 0	THEN _main.account	END ASC,
								CASE WHEN @sort_field = 1 AND @sort_order = 1	THEN _main.account	END DESC,
								CASE WHEN @sort_field = 2 AND @sort_order = 0	THEN (_main.name_l + _main.name_f)  END ASC,
								CASE WHEN @sort_field = 2 AND @sort_order = 1	THEN (_main.name_l + _main.name_f)	END DESC) AS id_row_local,
				_master.id, 
				_master.id_key,
				_main.account, 
				_main.name_f,
				_main.name_l,
				_main.name_m,
				_main.department,
				_main.details,
				_main.status,
				_master.create_time
			INTO #cache_primary
			FROM dbo.tbl_account _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 
			WHERE _master.active = 1 AND ((_main.name_f + ' ' + _main.name_l LIKE '%'+@name_like+'%') OR (_main.name_l + ' ' + _main.name_f LIKE '%'+@name_like+'%') OR (_main.name_l + ', ' + _main.name_f LIKE '%'+@name_like+'%') OR (@name_like IS NULL)) 

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@page_current,
				@page_rows
	
	
