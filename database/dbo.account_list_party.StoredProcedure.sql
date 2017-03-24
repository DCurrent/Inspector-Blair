USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_list_party]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-07-13
-- Description:	Get list of party (users who may be responsible for areas) accounts for drop lists and other form sources.
-- =============================================

CREATE PROCEDURE [dbo].[account_list_party]
	
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
						-- Lab supervisor
						-- Principal investigator
                    And _role.role IN ('6b865f32-c1d0-40b1-a14e-78d8c8491075','cd0a6b6c-ed15-40c0-95be-cbf0953a593e')
                ))
    
	-- Execute paging SP to output unpaged records.
	EXEC paging_basic
			-1 -- @page_current	
	
	
	
GO
