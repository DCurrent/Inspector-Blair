USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_list]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_list]
	
	-- Parameters
	
	-- paging
	@page_current		int				= 1,
	@page_rows			int				= 10,	
	
	-- filter
	@inspector			uniqueidentifier				= NULL,	
	@update_from		datetime2		= NULL,
	@update_to			datetime2		= NULL,
	@status				varchar(max)	= NULL,
	@building			varchar(4)		= NULL,
	
	-- sorting
	@sort_field			tinyint 		OUTPUT,
	@sort_order			bit				OUTPUT
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters	
		
		-- Sorting field.	
		IF		@sort_field IS NULL 
				OR @sort_field = 0 
				OR @sort_field > 6 SET @sort_field = 6
		
		-- Sorting order.	
		IF		@sort_order IS NULL SET @sort_order = 1
		
		-- Current page.
		IF		@page_current IS NULL SET @page_current = 1
		ELSE IF @page_current < 1 SET @page_current = 1

		-- Rows per page maximum.
		IF		@page_rows IS NULL SET @page_rows = 10
		ELSE IF @page_rows < 1 SET @page_rows = 10


	-- Status filter. You can't use variables in IN() and
	-- I refuse to sink to a dynamic query. Instead, we'll
	-- break down the string given to us from application
	-- using XML, and create a temp table with resulting values.
	-- We can the SELECT the table inside of IN() and fitler
	-- for all items given to us by user. 
		DECLARE @status_filter TABLE (id uniqueidentifier)   
		DECLARE @Delimiter CHAR = ','

		IF @status <> ''
			BEGIN
				INSERT INTO @status_filter SELECT LTRIM(RTRIM(Split.temp_value.value('.', 'VARCHAR(max)'))) 'filter_value' 
				FROM  
				(     
					 SELECT CAST ('<value>' + REPLACE(@status, @Delimiter, '</value><value>') + '</value>' AS XML) AS _whatever            
				) AS temp_value 
				CROSS APPLY _whatever.nodes ('/value') AS Split(temp_value)
			END

		
	-- Set up temp table so we can reuse results.
	CREATE TABLE #cache_primary
	(
		row_id				int,
		id					uniqueidentifier,		
		status				uniqueidentifier,
		status_label		varchar(50),
		room_id				varchar(10),
		room_code			varchar(6),
		building_code		varchar(4),
		building_name		varchar(20),	
		label				varchar(50),	
		log_update			datetime2,
		inspection_type		uniqueidentifier,
		inspection_type_label varchar(50)
	)		

	
	-- Populate main table var. with assembled recordset. This is where we'll
	-- perform filter and sort operations.
	INSERT INTO #cache_primary (row_id, 
							id, 
							status, 
							status_label, 
							room_id, 
							room_code, 
							building_code, 
							building_name, 
							label,
							log_update, 
							inspection_type,
							inspection_type_label)
	(SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.
								CASE WHEN @sort_field = 1 AND @sort_order = 0	THEN _main.label			END
								)			
	
		AS _row_number,
			_main.id, 
			_status.id,
			_status.label,			
			_room.RoomID,
			_main.room_code,
			_building.BuildingCode,
			_building.BuildingName,
			_main.label,			
			dbo.get_log_update_time(_main.id),
			_main.inspection_type,
			_type.label
	FROM dbo.tbl_inspection_primary _main 
		LEFT JOIN
			UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
        LEFT JOIN
			UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode                      
        LEFT JOIN
			dbo.tbl_event_type AS _status ON dbo.get_inspection_status(_main.id) = _status.id
		LEFT JOIN
			dbo.tbl_audit_question_inclusion_list AS _type ON _main.inspection_type = _type.id                        
	WHERE (dbo.get_log_update_time(_main.id) >= @update_from OR @update_from IS NULL OR @update_from = '') 
			AND (_status.id IN (SELECT id FROM @status_filter) OR @status = '')
			AND (_building.BuildingCode	= @building			OR @building	IS NULL OR @building = '-1')
			
			-- Inspector visits are stored in a sub table, so we'll need to look for their exisitence 
			-- in that table here.
			AND (Exists(
                SELECT 1
                FROM dbo.tbl_inspection_primary_visit As _visit
                WHERE _visit.fk_id = _main.id
                    And _visit.visit_by = @inspector
                ) OR @inspector IS NULL OR @inspector = '00000000-0000-0000-0000-000000000000'))
	
	-- Execute paging SP to output paged records and control data.
		EXEC paging_basic
			@page_current,
			@page_rows


GO
