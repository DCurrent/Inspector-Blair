USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get single record detail.
-- =============================================

CREATE PROCEDURE [dbo].[area]
	
	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL
		
AS	
	SET NOCOUNT ON;
	
	(SELECT ROW_NUMBER() OVER(ORDER BY _building.BuildingName, _area.roomID) 
		AS _row_number,
			_master.id,
			_master.id_key, 
			_master.create_time,
			_master.update_time,
			_master.active,
			_main.label, 
			_main.details,		
			_area.LocationBarCodeID				AS code,
			_area.floor,
			_area.RoomID						AS room_code,
			_building.BuildingCode				AS building_code,
			dbo.InitCap(_building.BuildingName)	AS building_name,
			_area.RoomUsage						AS use_code,
			dbo.InitCap(_use.UsageCodeDescr)	AS use_description_short,				
			_main.radiation_usage,
			_main.x_ray_usage,
			_main.hazardous_waste_generated,
			_main.chemical_lab_class,
			_main.chemical_operations_class,
			_main.ibc_protocal,
			_main.biosafety_level,
			_main.nfpa45_lab_unit,
			_main.laser_usage	
	INTO #cache_primary				
	FROM tbl_area _main	
	JOIN tbl_master _master ON _main.id_key = _master.id_key
	
		LEFT JOIN
                      UKSpace.dbo.Rooms AS _area ON _area.LocationBarCodeID = _main.code
                      LEFT JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _area.Building = _building.BuildingCode
					  LEFT JOIN
                      UKSpace.dbo.MasterRoomUsageCodes AS _use ON _area.RoomUsage = _use.UsageCode

	WHERE -- Normal filter. This produces an active 
			-- revision list of all records.
			(@param_filter_id_key IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key))

	-- Navigation. This executes the navigation
	-- procedure, which produces a recordset
	-- including next ID, last ID, etc. for
	-- use by the control code to create record
	-- navigation buttons. See the stored 
	-- procedure for details.
		EXEC master_navigation @param_filter_id

	-- Main detail	
	SELECT	
		* 
	FROM 
		#cache_primary _data 
	
	-- Sub Tables
		DECLARE @id_key int = NULL
		SET @id_key = (SELECT TOP 1 id_key FROM #cache_primary WHERE id = @param_filter_id)
			
			-- Biological agents
			
				-- We'll need to tie the subsets to 
				-- their respective source tables. To 
				-- do that we need the list of source
				-- items as a complete record set
				-- joined to master table.
				SELECT				 
					_main.details, 
					_master.id,
					_master.id_key, 
					_main.label  
				INTO #cache_biological_agent_source 
				FROM tbl_biological_host_source AS _main 
					JOIN 
						tbl_master AS _master ON _master.id_key = _main.id_key 
				WHERE _master.active = 1

				-- Now get the subset records for the main
				-- record by matching main record's key id
				-- to subset's foreign key. Finally, we tie
				-- in the source list record set via the
				-- item field.
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_list.label,
					_list.details
				FROM tbl_area_biological_agent _main    
					LEFT JOIN
						-- Join the subset's source table
						-- to get complete data (label, detail, etc.).
						#cache_biological_agent_source AS _list ON _list.id = _main.item  
				-- Filter to get only sub records related to main record set.
				WHERE _main.fk_id = @id_key

			-- Types
			SELECT 
				_master.id_key	AS id_key,
				_master.id		AS id,
				_main.label		AS label,
				_main.details	AS details				
			INTO #cache_type_source
			FROM dbo.tbl_area_type_source AS _main
				JOIN tbl_master _master ON _main.id_key = _master.id_key
			WHERE _master.active = 1 

					
			SELECT ROW_NUMBER() OVER(ORDER BY _source.label) 
				AS _row_number,
					_source.id, 
					_source.label, 
					_source.details,
					_main.item	AS selected -- We don't really care about the value, just if there is one. This column will be null if there is not a match between the list of items and the items actually in main record's sub table.
			INTO #cache_sub_type									
			FROM tbl_area_type _main			
			RIGHT OUTER JOIN
                      #cache_type_source AS _source ON _source.id = _main.item 
						AND _main.fk_id = @id_key

			SELECT	
				* 
			FROM 
				#cache_sub_type _data
		
			

GO
