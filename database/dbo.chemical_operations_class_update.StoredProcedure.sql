USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[chemical_operations_class_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[chemical_operations_class_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(25)		= NULL,
	@param_details			varchar(max)	= NULL
			

AS
BEGIN
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

	-- Local cache of master result.
	CREATE TABLE #cache_update
	(
		id_row	int,
		id_key	int,
		id		int
	)

	-- Update master table. This creates a new
	-- version entry and primary key we need.
		INSERT INTO #cache_update
			EXEC master_update
				@param_id_list,
				@param_update_by,
				@param_update_host

	-- Update data table using the
	-- new primary key from master table.
		INSERT INTO tbl_chemical_operations_class_source
				(id_key,
				label,
				details)	

		SELECT _master.id_key,
				@param_label,
				@param_details
		FROM 
			#cache_update AS _master

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_update AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END

GO
