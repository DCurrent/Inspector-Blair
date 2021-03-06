USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[config_form_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[config_form_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(50)		= NULL,
	@param_details			varchar(max)	= NULL,
	@param_title			varchar(50)		= NULL,
	@param_description		varchar(max)	= NULL,
	@param_main_sql_name	varchar(50)		= NULL,
	@param_main_object_name	varchar(50)		= NULL,
	@param_slug				varchar(50)		= NULL,
	@param_file_name		varchar(50)		= NULL
			

AS
BEGIN
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

	-- Local cache of master result.
	CREATE TABLE #cache_master_update_result
	(
		id_row	int,
		id_key	int,
		id		int
	)

	-- Update master table. This creates a new
	-- version entry and primary key we need.
		INSERT INTO #cache_master_update_result
			EXEC master_update
				@param_id_list,
				@param_update_by,
				@param_update_host

	-- Update data table using the
	-- new primary key from master table.
		INSERT INTO dbo.tbl_config_form
				(id_key,
				label,
				details,
				title,
				description,
				main_sql_name,
				main_object_name,
				slug,
				file_name)	

		SELECT _master.id_key,
				@param_label,
				@param_details,
				@param_title,
				@param_description,
				@param_main_sql_name,
				@param_main_object_name,
				@param_slug,
				@param_file_name
		FROM 
			#cache_master_update_result AS _master

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_master_update_result AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END

GO
