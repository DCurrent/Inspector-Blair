USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_lookup]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single account detail by account. No subtable info.
-- =============================================

CREATE PROCEDURE [dbo].[account_lookup]
	
	-- filter
	@account			varchar(10)		= NULL	
	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		-- Filters.		
		
		-- Sorting field.		
		
		-- Sorting order.		
		
	-- Set up table var so we can reuse results.		
	CREATE TABLE #cache_primary
	(
		account		varchar(10),
		department	varchar(5),
		details		varchar(max),
		name_f		varchar(25),
		name_l		varchar(25),
		name_m		varchar(25),
		status		int,
		row_id		int,
		id			int,
		log_update	datetime2
	)		
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO #cache_primary (id, account, department, details, status, name_f, name_l, name_m,  log_update)
		SELECT
				_master.id, 
				_main.account, 
				_main.department,
				_main.details,
				_main.status,
				_main.name_f,
				_main.name_l,
				_main.name_m,			 
				_master.create_time	
		FROM dbo.tbl_account _main JOIN tbl_master _master ON _main.id_key = _master.id_key AND _master.active = 1
		WHERE _main.account = @account
		
	
	-- Main detail	
	SELECT	
		* 
	FROM 
		#cache_primary _data
	 
	
	
GO
