USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_list]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[area_list]
	
	-- paging
	@param_page_current		int				= 1,
	@param_page_rows		int				= 10,
	
	-- Sorting
	@param_sort_field		tinyint			= 1,
	@param_sort_order		bit				= 1,
	
	-- Filters
	@param_date_start		datetime2		= NULL,
	@param_date_end			datetime2		= NULL,		
	@param_building_id		char(4)			= NULL,
	@param_floor			varchar(3)		= NULL,
	@param_room_id			char(10)		= NULL
	
AS	
	SET NOCOUNT ON;
		
	-- Create and Populate main table var. This is the primary query. Order
	-- and query details go here.
		(SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 0	THEN _master.create_time	END ASC,
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 1	THEN _master.create_time	END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _main.label			END ASC ,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _main.label			END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _building.BuildingName	END ASC ,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _building.BuildingName	END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _room.floor			END ASC ,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _room.floor			END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _room.RoomID			END ASC ,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _room.RoomID			END DESC)
			AS	_row_id,
				_master.id, 
				_master.id_key,
				_master.create_time,
				_master.active,
				_main.label, 
				_main.details,
				_main.code,
				_room.floor,
				_room.RoomID			AS room_id,
				_building.BuildingCode	AS building_id,
				dbo.InitCap(_building.BuildingName)	AS building_name			
		INTO #cache_primary
		FROM dbo.tbl_area AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key 
			INNER JOIN
                      UKSpace.dbo.Rooms AS _room ON _main.code = _room.LocationBarCodeID
                      INNER JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
		WHERE (_master.active = 1	AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL))
									AND (_building.BuildingCode	= @param_building_id	OR @param_building_id	IS NULL		OR @param_building_id	= '-1')
									AND (_room.floor			= @param_floor			OR @param_floor			IS NULL		OR @param_floor			= '')
									AND (_room.RoomID			= @param_room_id		OR @param_room_id		IS NULL		OR @param_room_id		= ''))
	
	-- Execute paging SP to output paged records and control data.
	EXEC master_paging
			@param_page_current,
			@param_page_rows


	

GO
