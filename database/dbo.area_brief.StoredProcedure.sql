USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_brief]    Script Date: 2017-08-09 11:38:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single record detail.
-- =============================================

CREATE PROCEDURE [dbo].[area_brief]
	
	-- Sorting
	@sort_field				tinyint		= 0,
	@sort_order				bit			= 0,

	-- filter
	@param_filter_fk_id		int	= NULL
		
AS	
	SET NOCOUNT ON;

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

				WHERE _main.fk_id = @param_filter_fk_id


				