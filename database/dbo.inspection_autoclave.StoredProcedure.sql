USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_autoclave]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-07
-- Description:	Get single inspection detail.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_autoclave]
	
	-- filter
	@id					int				= NULL,
	@inspection_type	smallint		= NULL,	
	
	-- sorting
	@sort_field			tinyint 		= NULL OUTPUT,
	@sort_order			bit				= NULL OUTPUT,
	
	-- Navigation
	-- sorting
	@nav_first			tinyint 		= NULL OUTPUT,
	@nav_previous		tinyint			= NULL OUTPUT,
	@nav_next			tinyint			= NULL OUTPUT,
	@nav_last			tinyint			= NULL OUTPUT
	
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
	
	-- We'll use this below for getting navigation ID's without rerunning the same SELECT query.
	DECLARE @row_current int = 0
	
	-- Set up table var so we can reuse results.		
	DECLARE @tempMain TABLE
	(
		row					int,
		id					int, 
		label				varchar(255), 
		details				varchar(max),
		room_code			varchar(6),
		room_code				varchar(10),
		building_code		varchar(4),
		building_name		varchar(20),
		status				tinyint,
		status_label		varchar(50),
		log_create			datetime2, 
		log_update			datetime2,
		inspection_type		smallint
	)		
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO @tempMain (row, 
							id, 
							label, 
							details, 
							room_code, 
							room_code, 
							building_code, 
							building_name, 
							status, 
							status_label,
							log_create, 
							log_update,
							inspection_type)
	(SELECT ROW_NUMBER() OVER(ORDER BY _main.log_create) 
		AS _row_number,
			_main.id, 
			_main.label, 
			_main.details,
			_main.room_code,
			_room.RoomID,
			_building.BuildingCode,
			_building.BuildingName,
			_main.status,
			_status.label,
			_main.log_create, 
			_main.log_update,
			_main.inspection_type			
	FROM dbo.tbl_inspection_primary _main LEFT JOIN
                      UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
                      LEFT JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
                      LEFT JOIN
                      dbo.tbl_inspection_status_list AS _status ON _main.status = _status.id
	WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0)
			AND (_main.inspection_type = @inspection_type))
	
	-- Main detail	
	SELECT	
		* 
	FROM 
		@tempMain _data	
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
		maker,
		serial,
		tag,
		model,
		biowaste
	FROM 
		tbl_inspection_autoclave_detail
	WHERE 
		fk_id = @id AND (record_deleted IS NULL OR record_deleted = 0) 
	ORDER BY 
		id DESC
			
	-- Navigation
		-- Get the current row.
		SELECT @row_current = (SELECT row FROM @tempMain WHERE id = @id)
		
		--First
		SELECT @nav_first = (SELECT TOP 1 id FROM @tempMain)
	
		-- Last
		SELECT @nav_last = (SELECT TOP 1 id FROM @tempMain ORDER BY row DESC)
		
		-- Next
		SELECT @nav_next = (SELECT TOP 1 id FROM @tempMain WHERE row > @row_current)
		
		-- Previous
		SELECT @nav_previous = (SELECT TOP 1 id FROM @tempMain WHERE row < @row_current ORDER BY row DESC)
GO
