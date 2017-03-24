USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_saa_area_list_unpaged]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Created: 2015-11-16
-- Description: Return list of items, sorted, and unpaged.
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

CREATE PROCEDURE [dbo].[inspection_saa_area_list_unpaged]

	-- Parameters
	@category	int	= NULL
	
AS
	
	SET NOCOUNT ON
	
	-- Set up table var so we can reuse results if needed.
	CREATE TABLE #cache_primary
	(
		row_id		int,
		id			uniqueidentifier,
		label		varchar(255)
	)
	
	-- Populate main table var. This is the primary query. order
	-- and filter should be placed here if possible.
	INSERT INTO #cache_primary (row_id,
							id,
							label)
		(SELECT ROW_NUMBER() OVER(ORDER BY _main.label)
			AS _row_number,
				_main.id,
				_main.label
			FROM dbo.tbl_inspection_saa_area_list _main			
			WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0))
			
	-- Now select from temp table.
	SELECT * 
	FROM #cache_primary
	
	
		
GO
