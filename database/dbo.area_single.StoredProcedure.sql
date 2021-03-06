USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_single]    Script Date: 2017-08-08 17:20:35 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get single record.
-- =============================================

ALTER PROCEDURE [dbo].[area_single]
	
	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL,
	@param_filter_room_code	varchar(6) = NULL
		
AS	
	SET NOCOUNT ON;
	
	-- If the ID is -1 or blank, and we have a room code,
	-- let's try and look up an ID from our local
	-- area table.
	IF((@param_filter_id = -1 OR @param_filter_id IS NULL) AND @param_filter_room_code IS NOT NULL)
	BEGIN
		SELECT @param_filter_id = _master.id FROM tbl_area _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key
		WHERE _main.code = @param_filter_room_code  AND _master.active = 1
	END	

	(SELECT ROW_NUMBER() OVER(ORDER BY _building.BuildingName, _area.roomID) 
		AS _row_number,
			_master.id,
			_master.id_key, 
			_master.create_time,
			_master.update_time,
			_master.active,
			_main.label, 
			_main.details,		
			_area.LocationBarCodeID				AS room_code,
			_area.floor,
			_area.RoomID						AS room_id,
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
			(@param_filter_id IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key))
	
	-- If there is nothing in the temp table at this point,
	-- then we do not have a local record at all. We still want 
	-- to return information from external database, so let's 
	-- try a query using the room code.
	IF NOT exists(SELECT 1 from #cache_primary WHERE id = @param_filter_id)
	BEGIN
		-- Query by the room code and insert results. Worst
			-- case is we don't have a code either and
			-- just get another blank.
			INSERT INTO #cache_primary
			(id,
			id_key,
			create_time,
			update_time,
			active,
			room_code,
			floor,
			room_id,
			building_code,
			building_name,
			use_code,
			use_description_short)				
			
			SELECT
				-1,	-- New ID Key								
				-1,	-- New ID
				GETDATE(),
				GETDATE(),
				1,
				_area.LocationBarCodeID,
				_area.floor,
				_area.RoomID,
				_building.BuildingCode,
				dbo.InitCap(_building.BuildingName),
				_area.RoomUsage,
				dbo.InitCap(_use.UsageCodeDescr)			
			FROM UKSpace.dbo.Rooms AS _area
								LEFT JOIN
								UKSpace.dbo.MasterBuildings AS _building ON _area.Building = _building.BuildingCode
								LEFT JOIN
								UKSpace.dbo.MasterRoomUsageCodes AS _use ON _area.RoomUsage = _use.UsageCode
			WHERE _area.LocationBarCodeID = @param_filter_room_code
	END	
	--ELSE
	--BEGIN
		-- Only for debugging. Make sure to disable
		-- this for production.
		-- SELECT * from #cache_primary
	--END

	
SELECT * FROM #cache_primary
			
