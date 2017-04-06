USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary]    Script Date: 2017-04-06 01:05:31 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single record detail.
-- =============================================

ALTER PROCEDURE [dbo].[inspection_primary]
	
	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL,
	@param_filter_type		int = NULL
		
AS	
	SET NOCOUNT ON;

	-- Create and populate the main data cache. This is 
	-- where we will do most (if not all) of our JOINs, 
	-- sorting and filtering to create a complete record set of
	-- primary data for consumption. We use a temporary
	-- table for performance and convenience. This temp table
	-- is also available in any other procedures we might call
	-- while this one is running. If we remember to use a 
	-- consistent naming convention, that will in turn allow us 
	-- to encapsulate a lot of repetitive work into reusable sub 
	-- procedures and keep their parameters to a bare minimum.		
		SELECT			
				_master.id,
				_master.id_key, 
				_master.active,
				_main.label, 
				_main.details,
				_master.create_time,
				_type.item AS type
		INTO #cache_primary					
		FROM dbo.tbl_inspection_primary AS _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key
			JOIN tbl_inspection_primary_type _type ON _type.fk_id = _master.id_key
		WHERE
			-- Normal filter. This produces an active 
			-- revision list of all records.
			((@param_filter_id_key IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key))
			
			AND _type.item = @param_filter_type			
		ORDER BY _main.label
			
	-- Navigation. This executes the navigation
	-- procedure, which produces a recordset
	-- including next ID, last ID, etc. for
	-- use by the control code to create record
	-- navigation buttons. See the stored 
	-- procedure for details.
		EXEC master_navigation @param_filter_id

	-- Select and output recordsets of data.

		-- Main (primary) data. We've already done all of
		-- the data processing. Just output the recordset
		-- filtered with ID.
		SELECT
			* 
		FROM 
			#cache_primary AS _data
		WHERE _data.id = @param_filter_id	
	
		-- Subsets. Once all the work is done for our primary table 
		-- and associated ancillary functionality, we can now output
		-- any sub data record sets. 
		
			-- First thing we need is the key id to
			-- relate to the the subsets foreign keys.
			-- We'll grab that from the finished main 
			-- record set and store it as a variable.
			DECLARE @id_key int = NULL
			SET @id_key = (SELECT TOP 1 id_key FROM #cache_primary WHERE id = @param_filter_id)

			-- Responsible parties	
				SELECT
					_master.id_key,
					_master.id,
					_main.name_f,
					_main.name_l
				INTO #cache_source_account
				FROM tbl_account _main
					JOIN tbl_master _master ON _main.id_key = _master.id_key

				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_account.name_f,
					_account.name_l
				FROM tbl_inspection_primary_party _main 
					JOIN #cache_source_account _account ON _account.id = _main.item

			-- Visit
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.visit_by,
					_main.visit_type,
					_main.time_recorded
				FROM tbl_inspection_primary_visit _main    
				WHERE _main.fk_id = @id_key

			-- Details				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.label,
					_main.details,
					_main.correction,
					_main.complete
				FROM tbl_inspection_primary_detail _main
				WHERE _main.fk_id = @id_key

			-- Area information		
				-- Source tables.
					-- Area.
					SELECT 
						_main.biosafety_level,
						_main.chemical_lab_class,
						_main.chemical_operations_class,
						_main.code,
						_main.details,
						_main.hazardous_waste_generated,
						_main.ibc_protocal,
						_main.id_key,
						_main.label,
						_main.laser_usage,
						_main.nfpa45_lab_unit,
						_main.radiation_usage,
						_main.x_ray_usage
					INTO
						#inspection_cache_tbl_area					
					FROM tbl_area AS _main 
						JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _master.active = 1

					-- Biosafety Level
					SELECT 
						_master.id,
						_main.label,
						_main.details
					INTO
						#inspection_cache_tbl_biosafety_level_source					
					FROM tbl_biosafety_level_source AS _main 
						JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _master.active = 1

					-- Chemical Lab Class
					SELECT 
						_master.id,
						_main.label,
						_main.details
					INTO
						#inspection_cache_tbl_chemical_lab_class_source					
					FROM tbl_chemical_lab_class_source AS _main 
						JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _master.active = 1

					-- Chemical Operations Class
					SELECT 
						_master.id,
						_main.label,
						_main.details
					INTO
						#inspection_cache_tbl_chemical_operations_class_source					
					FROM tbl_chemical_operations_class_source AS _main 
						JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _master.active = 1

					-- NFPA45 Lab Unit Hazard Class
					SELECT 
						_master.id,
						_main.label,
						_main.details
					INTO
						#inspection_cache_tbl_lab_unit_hazard_class_source				
					FROM tbl_lab_unit_hazard_class_source AS _main 
						JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _master.active = 1

		

				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.code AS room_code,	
					_area_local.label,				
					_building.BuildingCode AS building_code,
					_building.BuildingName AS building_name,
					_area_local.hazardous_waste_generated,
					_area_local.ibc_protocal,
					_area_local.laser_usage,
					_area_local.radiation_usage,
					_area_local.x_ray_usage,
					_biosafety_level.label AS biosafety_level,
					_chemical_lab_class.label AS chemical_lab_class,
					_chemical_operations_class.label AS chemical_operations_class,
					_lab_unit_hazard_class.label AS nfpa45_lab_unit,
					_department.DeptNo + ' - ' + _department.DeptName AS department
					-- _area_local.id_key AS area_id_key -- DEBUG only.
				FROM tbl_inspection_primary_area _main					
					LEFT JOIN
						#inspection_cache_tbl_area AS _area_local ON _main.code = _area_local.code
					LEFT JOIN
						UKSpace.dbo.Rooms AS _room ON _main.code = _room.LocationBarCodeID
					LEFT JOIN
						UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
					LEFT JOIN
						UKSpace.dbo.MasterDepartment AS _department ON _department.DeptNo = _room.Department
					LEFT JOIN
						#inspection_cache_tbl_biosafety_level_source AS _biosafety_level ON _biosafety_level.id = _area_local.biosafety_level
					LEFT JOIN
						#inspection_cache_tbl_chemical_lab_class_source AS _chemical_lab_class ON _chemical_lab_class.id = _area_local.chemical_lab_class
					LEFT JOIN
						#inspection_cache_tbl_chemical_operations_class_source AS _chemical_operations_class ON _chemical_operations_class.id = _area_local.chemical_operations_class
					LEFT JOIN
						#inspection_cache_tbl_lab_unit_hazard_class_source AS _lab_unit_hazard_class ON _lab_unit_hazard_class.id = _area_local.nfpa45_lab_unit

				WHERE _main.fk_id = @id_key
				