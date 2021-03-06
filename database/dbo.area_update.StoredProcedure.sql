USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[area_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(25)		= NULL,
	@param_details			varchar(max)	= NULL,
	@param_code				varchar(6)		= NULL,
	@param_type				xml				= NULL,
	@param_biological_agent xml				= NULL,
	@param_radiation_usage	bit				= NULL,
	@param_laser_usage		bit				= NULL,
	@param_x_ray_usage		bit				= NULL,
	@param_chemical_operations_class	int	= NULL,
	@param_chemical_lab_class	int	= NULL,
	@param_ibc_protocol		varchar(25)		= NULL,
	@param_biosafety_level	int				= NULL,
	@param_lab_unit_class	int				= NULL,
	@param_hazardous_waste_generated bit	= NULL
			

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
		INSERT INTO tbl_area
				(id_key,
				label,
				details,
				code,
				radiation_usage,
				laser_usage,
				x_ray_usage,
				chemical_operations_class,
				chemical_lab_class,
				ibc_protocal,
				biosafety_level,
				nfpa45_lab_unit,
				hazardous_waste_generated)	

		SELECT _master.id_key,
				@param_label,
				@param_details,
				@param_code,
				@param_radiation_usage,
				@param_laser_usage,
				@param_x_ray_usage,
				@param_chemical_operations_class,
				@param_chemical_lab_class,
				@param_ibc_protocol,
				@param_biosafety_level,
				@param_lab_unit_class,
				@param_hazardous_waste_generated
		FROM 
			#cache_update AS _master

		-- Sub tables
		
			-- Let's get the id_key to work with, since
			-- sub records are tied via id_key -> fk_id.

			DECLARE @id_key int = NULL

			SET @id_key = (SELECT id_key FROM #cache_update)

			-- Type
				-- Populate table with values from XML string.
				INSERT INTO tbl_area_type (fk_id, item)
				(SELECT
					@id_key,
					row.value('@id', 'INT')	AS item

				FROM @param_type.nodes('root/row')Catalog(row))	
			
			-- Biological Agent				
				-- Populate table with values from XML string.
				INSERT INTO tbl_area_biological_agent (fk_id, item)
				(SELECT
					@id_key,
					row.value('@id', 'INT')	AS item

				FROM @param_biological_agent.nodes('root/row')Catalog(row))				

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_update AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END

GO
