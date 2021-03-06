USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_list_inspector]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-07-13
-- Description:	Get list of inspector accounts for drop lists and other form sources.
-- =============================================

CREATE PROCEDURE [dbo].[account_list_inspector]
	-- paging
	@param_page_current		int				= -1,
	@param_page_rows		int				= 10

AS	
	SET NOCOUNT ON;
	
	
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	SELECT
			_master.id,
			_master.id_key, 
			_main.account,
			_main.name_f,
			_main.name_l,
			_main.name_m
	INTO #cache_primary
	FROM dbo.tbl_account _main
	JOIN tbl_master _master ON _main.id_key = _master.id_key 
	WHERE (_master.active = 1)
		AND Exists(
                Select 1
                From dbo.tbl_account_role As _role
                Where _role.fk_id = _master.id_key
                    And _role.item IN (780, 785, 783, 781, 779)
                )
    ORDER BY _main.name_l, _main.name_f, _main.name_m 

	-- Execute paging SP to output paged records and control data.
	EXEC master_paging
			@param_page_current,
			@param_page_rows	
	
	
	
GO
