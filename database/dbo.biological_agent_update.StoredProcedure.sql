USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[biological_agent_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[biological_agent_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(50)		= NULL,
	@param_details			varchar(max)	= NULL,
	@param_information		varchar(max)	= NULL,
	@param_risk_group		int				= NULL,
	@param_host				xml				= NULL			

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
		INSERT INTO tbl_biological_agent
				(id_key,
				label,
				details,
				information,
				risk_group)	

		SELECT _master.id_key,
				@param_label,
				@param_details,
				@param_information,
				@param_risk_group
		FROM 
			#cache_master_update_result AS _master

	-- Sub Data
		-- Let's get the id_key to work with, since
		-- sub records are tied via id_key -> fk_id.

		DECLARE @id_key int = NULL

		SET @id_key = (SELECT id_key FROM #cache_master_update_result)

		-- Host
			-- Populate table with values from XML string.
			INSERT INTO tbl_biological_agent_host(fk_id, item)
			(SELECT
				@id_key,
				row.value('@id', 'INT')	AS item

			FROM @param_host.nodes('root/row')Catalog(row))

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_master_update_result AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END

GO
