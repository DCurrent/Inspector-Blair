USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[paging_basic]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[paging_basic]
	
	-- Parameters. 
		@page_current	int			= 1,	-- Current page of records to display.
		@page_rows		smallint	= 25	-- (optional) max number of records to display in a page.
			
AS
BEGIN
	
	-- If non paged layout is requested (current = -1), then just
	-- get all records. Otherwise, extract page of records as needed.
	IF @page_current = -1
		BEGIN
			SELECT *
				FROM #cache_primary
		END		
	ELSE
		BEGIN 

			-- Verify arguments from control code. If something
			-- goes out of bounds we'll use stand in values. This
			-- also lets the paging "jumpstart" itself without
			-- needing input from the control code.
				
				-- Current page default.
				IF	@page_current IS NULL OR @page_current < 1
					SET @page_current = 1
			
				-- Rows per page default.
				IF	@page_rows IS NULL OR @page_rows < 1 
					SET @page_rows = 10
							
			-- Declare the working variables we'll need.			
				
				DECLARE 
					@row_count_total	int,	-- Total row count of primary table.
					@page_last			float,	-- Number of the last page of records.
					@row_first			int,	-- Row ID of first record.
					@row_last			int		-- Row ID of last record.
			
			-- Get total count of records.
				
				SET @row_count_total = (SELECT COUNT(id_row) FROM #cache_primary);

			-- Get paging first and last row limits. Example: If current page
			-- is 2 and 10 records are allowed per page, the first row should 
			-- be 11 and the last row 20.
				
				SET @row_first	= (@page_current - 1) * @page_rows
				SET @row_last	= (@page_current * @page_rows + 1);			
	
			-- Get last page number.
				
				SET @page_last = (SELECT CEILING(CAST(@row_count_total AS FLOAT) / CAST(@page_rows AS FLOAT)))
				IF @page_last = 0 SET @page_last = 1								

			-- Extract paged rows from primary table var and output as recordset.
				
				SELECT TOP (@row_last-1) *
					FROM #cache_primary	 
					WHERE id_row > @row_first 
						AND id_row < @row_last
					
					ORDER BY id_row	
				
			-- Output the paging data as a recordset for use by control code.
				
				SELECT	@row_count_total	AS row_count_total,
						@page_rows			AS page_rows,
						@page_last			AS page_last,
						@row_first			AS row_first,
						@row_last			AS row_last
			
		END
END
GO
