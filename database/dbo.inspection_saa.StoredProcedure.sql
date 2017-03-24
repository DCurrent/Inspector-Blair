USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_saa]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get single inspection detail.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_saa]
	
	-- filter
	@id					uniqueidentifier = NULL,
	@inspection_type	uniqueidentifier = NULL,	
	
	-- sorting
	@sort_field			tinyint 		= NULL,
	@sort_order			bit				= NULL
		
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		-- Filters.		
		
		-- Sorting field.	
		IF		@sort_field IS NULL 
				OR @sort_field = 0 
				OR @sort_field > 4 SET @sort_field = 3
		
		-- Sorting order.	
		IF		@sort_order IS NULL SET @sort_order = 1	
	
	-- Set up table var so we can reuse results.		
	CREATE TABLE #cache_primary
	(
		row_id				int,
		id					uniqueidentifier, 
		label				varchar(255), 
		details				varchar(max),
		room_code			varchar(6),
		room_id				varchar(10),
		building_code		varchar(4),
		building_name		varchar(20),
		status				uniqueidentifier,
		status_label		varchar(50),		 
		log_update			datetime2,
		inspection_type		uniqueidentifier,
		radiation_usage		bit
	)		
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO #cache_primary (row_id, 
							id, 
							label, 
							details, 
							room_code, 
							room_id, 
							building_code, 
							building_name, 
							status, 
							status_label,
							log_update,
							inspection_type,
							radiation_usage)
	(SELECT ROW_NUMBER() OVER(ORDER BY log_update) 
		AS _row_number,
			_main.id, 
			_main.label, 
			_main.details,
			_main.room_code,
			_room.RoomID,
			_building.BuildingCode,
			_building.BuildingName,
			_status.id,
			_status.label, 
			dbo.get_log_update_time(_main.id),
			_main.inspection_type,
			_area.radiation_usage			
	FROM dbo.tbl_inspection_primary _main LEFT JOIN
                      UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
                      LEFT JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
                      LEFT JOIN
                      dbo.tbl_event_type AS _status ON dbo.get_inspection_status(_main.id) = _status.id
					  LEFT JOIN
                      dbo.tbl_area AS _area ON _main.room_code = _area.code
	WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0)
			AND (_main.inspection_type = @inspection_type))
	
	-- If ID filter parameter is blank, then we'll get the top row
	-- and use the its id value to populate the id parameter.
	IF @id IS NULL
	BEGIN
		-- Set ID parameter to this record id.
		SELECT @id = (SELECT TOP 1 id FROM #cache_primary)
	END


	-- Navigation
	-- For populating navigation buttons. We need to get the IDs for
	-- current record, next, previous, and bookends. This should come
	-- after all filtering and ordering is completed on the primary
	-- table cache, but be the first recordset output. 
	EXEC navigation @id

	-- Main detail	
	SELECT	
		* 
	FROM 
		#cache_primary	
	WHERE
		id = @id
	
	-- Sub table (parties)
	SELECT	_party.id, 
			_party.fk_id, 
			_party.party, 
			_account.name_f, 
			_account.name_m, 
			_account.name_l
	FROM 
		tbl_inspection_primary_party _party INNER JOIN
                      tbl_account _account ON _party.party = _account.id
	WHERE 
		fk_id = @id AND (_party.record_deleted IS NULL OR _party.record_deleted = 0)
	ORDER BY
		_account.name_l, _account.name_f 
	
	-- Sub table (visits)
	SELECT 
		id,
		visit_by,
		visit_type,
		time_recorded
	FROM 
		tbl_inspection_primary_visit
	WHERE 
		fk_id = @id AND (record_deleted IS NULL OR record_deleted = 0) 
	ORDER BY 
		time_recorded
		
	-- Sub table (detail)
	SELECT 
		id,
		label,
		details,
		correction,
		complete
	FROM 
		tbl_inspection_saa_detail
	WHERE 
		fk_id = @id AND (record_deleted IS NULL OR record_deleted = 0)
GO
