USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_list_unpaged]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-05-24
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[area_list_unpaged]
	
	-- Parameters
	
	-- paging
	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters		
		
		-- Current page.
		

	-- Determine the first record and last record 
	
	
	-- Set up table var so we can reuse results.		
	DECLARE @temp_union TABLE
	(		
		code			varchar(6),
		floor			varchar(3),
		room_id			varchar(10),
		building_id		varchar(4),		
		building_name	varchar(20)
	)	

	DECLARE @temp_main TABLE
	(
		row				int,		
		code			varchar(6),
		floor			varchar(3),
		room_id			varchar(10),
		building_id		varchar(4),		
		building_name	varchar(20)
		
	)
	
	-- Populate union table var. 
	INSERT INTO @temp_union (code, floor, room_id, building_id, building_name)
	(SELECT
			_area.code,	
			_room.floor,
			_room.RoomID,
			_building.BuildingCode,
			_building.BuildingName
	FROM dbo.tbl_area _area	INNER JOIN
                      UKSpace.dbo.Rooms AS _room ON _area.code = _room.LocationBarCodeID
                      INNER JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
	WHERE (_area.record_deleted IS NULL OR _area.record_deleted = 0)
UNION
SELECT _inspection.room_code AS code,			
			_room.floor,
			_room.RoomID,
			_building.BuildingCode,
			_building.BuildingName
FROM tbl_inspection_primary _inspection INNER JOIN
                      UKSpace.dbo.Rooms AS _room ON _inspection.room_code = _room.LocationBarCodeID
                      INNER JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode)
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO @temp_main (row, code, floor, room_id, building_id, building_name)
	(SELECT ROW_NUMBER() OVER(ORDER BY 
								_main.building_name, 
								_main.floor, 
								_main.room_id) 
		AS _row_number,
			_main.code,	
			_main.floor,
			_main.room_id,
			_main.building_id,
			dbo.InitCap(_main.building_name)
	FROM @temp_union _main)	
	
	-- Extract paged rows from main tabel var.
	SELECT *
	FROM @temp_main	_main
	ORDER BY row
	
	
	
	

GO
