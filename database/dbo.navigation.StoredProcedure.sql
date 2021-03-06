USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[navigation]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-06-17
-- Description:	
-- =============================================

CREATE PROCEDURE [dbo].[navigation]

	-- filter
	@id					bigint	= NULL	

AS
	SET NOCOUNT ON;		
	
	-- Navigation
	-- For populating navigation buttons. We need to get the IDs for
	-- current record, next, previous, and bookends. This should come
	-- after all filtering and ordering is completed on the primary
	-- table cache, but be the first recordset output. 

	-- Get the current row number.
	DECLARE @row_current int = (SELECT id_row FROM #cache_primary WHERE id = @id)
	
	-- Create a table var for our IDs.
	DECLARE @temp_navigation TABLE
	(		
		id_current		bigint,
		id_first		bigint,
		id_last			bigint,
		id_next			bigint,
		id_previous		bigint
	)

	-- Get each type of ID we need for navigation and insert
	-- into navigation table.
	INSERT INTO @temp_navigation (id_current, 
									id_first, 
									id_last, 
									id_next, 
									id_previous)
		SELECT TOP 1	(SELECT TOP 1 id	FROM #cache_primary _primary WHERE		_primary.id = @id),
						(SELECT TOP 1 id	FROM #cache_primary _primary),
						(SELECT TOP 1 id	FROM #cache_primary _primary ORDER BY	_primary.id_row DESC),
						(SELECT TOP 1 id	FROM #cache_primary _primary WHERE		_primary.id_row > @row_current),
						(SELECT TOP 1 id	FROM #cache_primary _primary WHERE		_primary.id_row < @row_current ORDER BY id_row DESC)	
	FROM #cache_primary _primary

	-- Output the navigation table to a recordset.
	SELECT * FROM @temp_navigation
GO
