USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_role_list_unpaged]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get list of tickets, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_role_list_unpaged]
	
	-- Parameters
	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters.	
	
	-- Set up table var so we can reuse results.		
	DECLARE @tempMain TABLE
	(
		row int,
		id int, 
		label varchar(255)
	)	
	
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO @tempMain (row, id, label)
	(SELECT ROW_NUMBER() OVER(ORDER BY label) 
		AS _row_number,
			_main.id, 
			_main.label
	FROM dbo.tbl_inspection_saa_area_list _main
	WHERE (record_deleted IS NULL OR record_deleted = 0))
	
	-- Now select from temp table.
	SELECT *
	FROM @tempMain	 
	
GO
