USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_list_inspector]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-07-13
-- Description:	Get list of inspector accounts for drop lists and other form sources.
-- =============================================

CREATE PROCEDURE [dbo].[account_list_inspector]
	
AS	
	SET NOCOUNT ON;
	
	-- Set up table var so we can reuse results.		
	CREATE TABLE #cache_primary
	(
		row_id		int,
		id			uniqueidentifier, 
		account		varchar(10),
		name_f		varchar(25),
		name_l		varchar(25),
		name_m		varchar(3)		
	)	
	
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO #cache_primary (row_id, id, account, name_f, name_l, name_m)
	(SELECT ROW_NUMBER() OVER(ORDER BY name_l, name_f) 
		AS _row_number,
			_main.id, 
			_main.account,
			_main.name_f,
			_main.name_l,
			_main.name_m
	FROM dbo.tbl_account _main
	WHERE (record_deleted IS NULL OR record_deleted = 0)
		AND Exists(
                Select 1
                From dbo.tbl_account_role As _role
                Where _role.fk_id = _main.id
                    And _role.role IN ('60d114b5-7ffb-4348-b4b3-784d554ad6fa','244370d8-b6b8-4bad-bff5-327ccdb20e41','62368d7d-2824-4cbc-ab92-11c28e012a03','b44fbdee-a121-400a-a8e5-a860f3d3a033','ecc5f26c-0aaf-4778-8278-ea2da6812d6b')
                ))
    
	-- Execute paging SP to output unpaged records.
	EXEC paging_basic
			-1 -- @page_current	
	
	
	
GO
