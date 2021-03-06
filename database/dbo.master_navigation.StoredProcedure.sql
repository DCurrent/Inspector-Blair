USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[master_navigation]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


CREATE PROCEDURE [dbo].[master_navigation]

	-- filter
	@id					int	= NULL	

AS
	SET NOCOUNT ON;		
	
	-- Navigation
	-- For populating navigation buttons. We need to get the IDs for
	-- current record, next, previous, and bookends. This should come
	-- after all filtering and ordering is completed on the primary
	-- table cache, but be the first recordset output. 

	-- Get the current row number.
		DECLARE @row_current int
	
	-- Create a table to populate with ordered row
	-- numbers and IDs taken from the primary data
	-- table.
		CREATE TABLE #cache_navigation
			(
				id_row				int,
				id					int
			)

	-- Populate paging cache. This is to add an
		-- ordered row number column we can use to 
		-- do paging math.
		INSERT INTO #cache_navigation (id_row, 
									id)
		(SELECT ROW_NUMBER() OVER(ORDER BY @@rowcount)
			AS id_row,
			id
		FROM #cache_primary _main)	


	-- Create a table var for our IDs.
		DECLARE @temp_navigation TABLE
		(		
			id_current		int,
			id_first		int,
			id_last			int,
			id_next			int,
			id_previous		int
		)

	-- Get the current row.
		SET @row_current = (SELECT id_row FROM #cache_navigation WHERE id = @id)

	-- Get each type of ID we need for navigation and insert
	-- into navigation table.
		INSERT INTO @temp_navigation (id_current, 
									id_first, 
									id_last, 
									id_next, 
									id_previous)
			SELECT			
				-- Current: No need for any esoteric calculation, it should always be the requested ID.
				(@id),

				-- First: Get first ID from recordset in ascending order.
				(SELECT TOP 1 id	FROM #cache_navigation _main),

				-- Last: Get first ID from recordset in descending order.
				(SELECT TOP 1 id	FROM #cache_navigation _main ORDER BY	_main.id_row DESC),

				-- Next: Get the first ID in the recordset we can find that 
				-- is > than the current ID, with recordset in ascending order.
				(SELECT TOP 1 id	FROM #cache_navigation _main WHERE		_main.id_row > @row_current),

				-- Previous: Get the first ID in recordset we can find that 
				-- is < than current ID, with recordset in descending order.
				(SELECT TOP 1 id	FROM #cache_navigation _main WHERE		_main.id_row < @row_current ORDER BY id_row DESC)	
	
	-- Output the navigation table to a recordset.
	SELECT * FROM @temp_navigation
GO
