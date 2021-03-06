USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[account_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_account			varchar(10)		= NULL,
	@param_department		char(5)			= NULL,
	@param_details			varchar(max)	= NULL,
	@param_name_f			varchar(25)		= NULL,
	@param_name_l			varchar(25)		= NULL,
	@param_name_m			varchar(25)		= NULL,	
	@param_sub_role_list	xml				= NULL
			

AS
BEGIN
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

	-- Local cache of master result.
	CREATE TABLE #cache_account_update
	(
		id_row	int,
		id_key	int,
		id		int
	)

	-- Update master table. This creates a new
	-- version entry and primary key we need.
		INSERT INTO #cache_account_update
			EXEC master_update
				@param_id_list,
				@param_update_by,
				@param_update_host

	-- Update data table using the
	-- new primary key from master table.
		INSERT INTO tbl_account
				(id_key,
				account, 
				department,
				name_f,
				name_l,
				name_m,
				details)	

		SELECT _master.id_key,
				@param_account, 
				@param_department,
				@param_name_f,
				@param_name_l,
				@param_name_m,
				@param_details
		FROM 
			#cache_account_update AS _master
		
		-- Sub records

			DECLARE @temp_xml xml = NULL

			-- Declare and set a foreign key. Sub records
			-- are keyed by the the main record's key ID, NOT 
			-- the group ID.
			DECLARE @fk_id int = NULL

			SET @fk_id = (SELECT TOP 1 id_key FROM #cache_account_update)
			
			-- Get the list of records to update and 
			-- push them into a local temp table.
				SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
					INTO #cache_account_role_update_item
					FROM @param_sub_role_list.nodes('root/row/@id') AS x(y) 					

				INSERT INTO tbl_account_role
						(fk_id, 
						item)
					SELECT @fk_id, 
							_source.item
					FROM #cache_account_role_update_item _source

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_account_update AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END

GO
