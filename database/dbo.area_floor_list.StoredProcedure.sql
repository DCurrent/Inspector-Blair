USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[area_floor_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[area_floor_list]
	
	-- paging
	@param_page_current		int				= -1,
	@param_page_rows		int				= 10
	
	-- Sorting
	
	-- Filters
	
AS	
	SET NOCOUNT ON;
		
	-- Create and Populate main table var. This is the primary query. Order
	-- and query details go here.
		(SELECT DISTINCT ROW_NUMBER() OVER(ORDER BY _building.BuildingName)
			AS	_row_id,
				_master.id, 
				_master.id_key,
				_master.create_time,
				_master.active,				
				_room.floor		
		INTO #cache_primary
		FROM dbo.tbl_area AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key 
			INNER JOIN
                      UKSpace.dbo.Rooms AS _room ON _main.code = _room.LocationBarCodeID
                      INNER JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
		WHERE (_master.active = 1))
	
	-- Execute paging SP to output paged records and control data.
	EXEC master_paging
			@param_page_current,
			@param_page_rows


	

GO
