USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[biowaste_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-08-15
-- Description:	Get list of items, optionally ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[biowaste_list]
	
	-- Parameters
	@paged				int				= 0,

	-- paging
	@page_current		int				= 1,
	@page_rows			int				= 10,	
	@page_last			float			OUTPUT,
	@row_count_total	int				OUTPUT	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters		
		
		-- Current page.
		IF		@page_current IS NULL SET @page_current = 1
		ELSE IF @page_current < 1 SET @page_current = 1

		-- Rows per page maximum.
		IF		@page_rows IS NULL SET @page_rows = 10
		ELSE IF @page_rows < 1 SET @page_rows = 10

	-- Determine the first record and last record 
	DECLARE @row_first int, 
			@row_last int
	
	-- Set up table var so we can reuse results.		
	CREATE TABLE #cache_primary
	(
		row_id		int,
		id			uniqueidentifier,
		log_update	datetime2,
		label		varchar(50)
	)	
	
	-- Populate paging first and last row limits.
	SELECT @row_first = (@page_current - 1) * @page_rows
	SELECT @row_last = (@page_current * @page_rows + 1);	
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO #cache_primary (row_id, id, log_update, label)
	(SELECT ROW_NUMBER() OVER(ORDER BY label) 
		AS _row_number,
			_main.id, 
			dbo.get_log_update_time(_main.id), 
			_main.label
	FROM dbo.tbl_biowaste_list _main
	WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0))	
	
	IF @paged = 1
		BEGIN
			-- Extract paged rows from main tabel var.
			SELECT TOP (@row_last-1) *
			FROM #cache_primary	 
			WHERE row_id > @row_first 
				AND row_id < @row_last
			ORDER BY row_id
	
			-- Get a count of records without paging. We'll need this for control
			-- code and for calculating last page. 
			SELECT @row_count_total = (SELECT COUNT(id) FROM #cache_primary);
	
			-- Get last page. This is for use by control code.
			SELECT @page_last = (SELECT CEILING(CAST(@row_count_total AS FLOAT) / CAST(@page_rows AS FLOAT)))
			IF @page_last = 0 SET @page_last = 1
		END
	ELSE
		BEGIN
			SELECT *
				FROM #cache_primary
		END
	

GO
