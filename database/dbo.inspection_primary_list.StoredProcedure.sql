USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary_list]    Script Date: 2017-07-29 11:31:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

ALTER PROCEDURE [dbo].[inspection_primary_list]
	
	-- Parameters
	
	-- paging
	@param_page_current			int				= 1,
	@param_page_rows			int				= 10,	

	-- Sorting
	@param_sort_field			tinyint			= 1,
	@param_sort_order			bit				= 0,

	-- filters
	@param_date_start			datetime2		= NULL,
	@param_date_end				datetime2		= NULL,
	@param_inspector			int				= NULL,	
	@param_status				varchar(max)	= NULL,
	@param_building				varchar(4)		= NULL
	
AS	
	SET NOCOUNT ON;
	
	-- Status filter. You can't use variables in IN() and
	-- I refuse to sink to a dynamic query. Instead, we'll
	-- break down the string given to us from application
	-- using XML, and create a temp table with resulting values.
	-- We can use the SELECT the table inside of IN() and fitler
	-- for all items given to us by user. 
		DECLARE @status_filter TABLE (id int)   
		DECLARE @Delimiter CHAR = ','

		IF @param_status <> ''
			BEGIN
				INSERT INTO @status_filter SELECT LTRIM(RTRIM(Split.temp_value.value('.', 'VARCHAR(max)'))) 'filter_value' 
				FROM  
				(     
					 SELECT CAST ('<value>' + REPLACE(@param_status, @Delimiter, '</value><value>') + '</value>' AS XML) AS _whatever            
				) AS temp_value 
				CROSS APPLY _whatever.nodes ('/value') AS Split(temp_value)
			END

		-- Combine the area subtable with UK Space database.
		SELECT
			_main.id_key,
			_main.fk_id,
			_main.code AS room_code,
			_room.RoomID AS room_id,
			_building.BuildingCode AS building_code,
			dbo.InitCap(_building.BuildingName) AS building_name
		INTO #inspection_list_cache_area
		FROM tbl_inspection_primary_area AS _main
			LEFT JOIN
				UKSpace.dbo.Rooms AS _room ON _main.code = _room.LocationBarCodeID
			LEFT JOIN
				UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
	
	-- We will need a list of accounts with ID from Master.
		SELECT	_master.id,
				_master.id_key,
				_main.label,
				_main.name_l + ', ' + name_f AS name_label
		INTO	#cache_account_list
		FROM tbl_account AS _main
		JOIN tbl_master _master ON _main.id_key = _master.id_key
		WHERE _master.active = 1
		ORDER BY _main.name_l, _main.name_f, _main.name_m



	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
		SELECT 
			_master.id, 
			_master.id_key,
			_main.label,
			_main.details,
			_master.create_time,
			_area.room_code,
			_area.room_id,
			_area.building_name,
			STUFF  ((SELECT DISTINCT ';' + CAST(visit_by AS VARCHAR(5))
                                 FROM            tbl_inspection_primary_visit _visit
                                 WHERE        _master.id_key = _visit.fk_id FOR XML PATH(''), TYPE, ROOT ).value('root[1]', 'varchar(max)'), 1, 1, '') AS visit_by_list,
			STUFF
                             ((SELECT DISTINCT '; ' +
                                            (SELECT _account.name_label
                                            FROM         #cache_account_list AS _account
                                            WHERE        _account.id = _visit.visit_by) AS name_label
                                 FROM         tbl_inspection_primary_visit AS _visit
                                 WHERE        _master.id_key = _visit.fk_id
                                 FOR XML PATH(''), TYPE, ROOT).value('root[1]', 'varchar(max)'), 1, 1, '') AS visit_by_list_label
		INTO #cache_peliminary
		FROM dbo.tbl_inspection_primary _main			
			JOIN tbl_master _master ON _main.id_key = _master.id_key 
			LEFT JOIN
					#inspection_list_cache_area AS _area ON _area.fk_id = _master.id_key				

		WHERE _master.active = 1 AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL)
			AND (_area.building_code = @param_building OR @param_building IS NULL)

		SELECT ROW_NUMBER() OVER(ORDER BY 
							-- Sort order options here. CASE lists are ugly, but we'd like to avoid
							-- dynamic SQL for maintainability.								
							CASE WHEN @param_sort_field = 0 AND @param_sort_order = 0	THEN create_time	END ASC,
							CASE WHEN @param_sort_field = 0 AND @param_sort_order = 1	THEN create_time	END DESC,
							CASE WHEN @param_sort_field = 1 AND @param_sort_order = 0	THEN label			END ASC,
							CASE WHEN @param_sort_field = 1 AND @param_sort_order = 1	THEN label			END DESC,
							CASE WHEN @param_sort_field = 3 AND @param_sort_order = 0	THEN building_name + room_id END ASC,
							CASE WHEN @param_sort_field = 3 AND @param_sort_order = 1	THEN building_name + room_id END DESC,
							CASE WHEN @param_sort_field = 4 AND @param_sort_order = 0	THEN visit_by_list_label	END ASC,
							CASE WHEN @param_sort_field = 4 AND @param_sort_order = 1	THEN visit_by_list_label	END DESC) AS id_row_local, 
		*
		INTO #cache_primary
		FROM #cache_peliminary

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows
	
