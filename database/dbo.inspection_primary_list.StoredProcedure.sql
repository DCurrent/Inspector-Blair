USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary_list]    Script Date: 2017-08-07 10:37:15 ******/
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
	@param_page_rows			int				= 100,	

	-- Sorting
	@param_sort_field			tinyint			= 1,
	@param_sort_order			bit				= 0,

	-- filters
	@param_date_start			datetime2		= NULL,
	@param_date_end				datetime2		= NULL,
	@param_inspector			xml				= NULL,	
	@param_status				xml				= NULL,
	@param_building				varchar(4)		= NULL
	
AS	
	SET NOCOUNT ON;

	-- Multi Item filters.
		--Visit by (Inspector).
			-- First let's create a temp table and give the ID 
			-- field an index for faster joining.
			CREATE TABLE #filter_visit_temp
			(	
				id			int,
				visit_by	int	
			)
			CREATE CLUSTERED INDEX IDX_filter_visit_id_key ON #filter_visit_temp(id)

			CREATE TABLE #filter_visit
			(	
				id			int	
			)
			CREATE CLUSTERED INDEX IDX_filter_visit_final_id ON #filter_visit(id)

							 
			-- If the filter argument is set, then we're going to
			-- create a filtered list of items and join them
			-- in the main query to act as a filter. If the argument
			-- is NULL, we'll need to make an unfiltered list instead. 
			IF @param_inspector IS NOT NULL
				BEGIN
									
					-- Create a temp table from the xml sent by application. We will
					-- join this table to our list of filter items, filtering them.
					--	<root>
					--		<row id = "1" />
					--		<row id = "2" />
					--		...
					--	</root>
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS id	
						INTO #filter_visit_arg
						FROM @param_inspector.nodes('root/row/@id') AS x(y)

					-- Build our list of filtered items. We need the ID of the main query's
					-- source table, and whatever JOINS are nessesary to aquire the item we
					-- want to filter by. We will then JOIN the filter we built earlier. This will
					-- limit the items here to matches from our filter list. We can then JOIN this 
					-- list in the main query by ID, filtering its results accordingly.
					INSERT INTO #filter_visit_temp
						SELECT DISTINCT       
							_master.id, 
							_filter.visit_by									
						FROM tbl_inspection_primary AS _main 
							JOIN 
								tbl_master _master ON _main.id_key = _master.id_key
							INNER JOIN
								tbl_inspection_primary_visit _filter ON _master.id_key = _filter.fk_id
							INNER JOIN 
								#filter_visit_arg AS _filter_arg ON _filter_arg.id = visit_by
						WHERE _master.active = 1
				END
				ELSE
					-- Since we have a JOIN to the final list of filtered items here, we need to 
					-- account for getting a NULL input from the application - otherwise no records
					-- at all would be returned from the main query. If the argument is NULL, this
					-- query populates the filter temp table just as above, except without filtering
					-- for results first. In effect, we get "all" results possible from this filter.
					INSERT INTO #filter_visit_temp
						SELECT DISTINCT       
							_master.id, 
							_filter.visit_by									
						FROM tbl_inspection_primary AS _main 
							JOIN 
								tbl_master _master ON _main.id_key = _master.id_key
							INNER JOIN
								tbl_inspection_primary_visit _filter ON _master.id_key = _filter.fk_id
						WHERE _master.active = 1
				
			-- The above queries will always produce duplicate IDs, which will
			-- result in duplicate entries from main query. Since all we need
			-- for joining to the main query is an ID, let's geta distinct list here.		
			INSERT INTO	#filter_visit
				SELECT DISTINCT id FROM #filter_visit_temp

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
	
	-- Visit By. Our visit by field must display
	-- all the names of visitors in a single column.
	-- This will have to be done using the STUFF method,
	-- but first we will need a list of accounts combined
	-- with IDs from the Master table.
		SELECT	_master.id,
				_master.id_key,
				_main.label,
				_main.name_l + ', ' + name_f AS name_label
		INTO	#cache_account_list
		FROM tbl_account AS _main
		JOIN tbl_master _master ON _main.id_key = _master.id_key
		WHERE _master.active = 1
		ORDER BY _main.name_l, _main.name_f, _main.name_m

	-- Status. "Status" is the latest visit type. 
		-- First thing we need to do is get a list of visit 
		-- types, combined with master table.
		SELECT	_master.id,
				_master.id_key,
				_main.label
		INTO	#cache_status_source
		FROM tbl_inspection_status_source AS _main
		JOIN tbl_master _master ON _main.id_key = _master.id_key
		WHERE _master.active = 1	
	
		-- Create our sub status list for the main query. 
		-- The source is visits subtable. There could be
		-- many visits, but we only want the most recent
		-- visit for each inspection. 
		
		-- Here we select the visit type label and forigen
		-- key from a subquery. By filtering for
		-- row number 1, we get only the most recent
		-- entry for a given forigen key (inspection). 
		SELECT _temp.fk_id, _temp.label 
		INTO	#sub_status 
			
			-- This Subquery gets us a list of visits in
			-- descending order by ID, and assigns each result
			-- a row number. The row number is what allows us
			-- to filter for the most recent. We also join
			-- status source list so we can display the label.
			FROM (SELECT ROW_NUMBER() OVER(PARTITION BY _visit.fk_id ORDER BY _visit.id_key DESC) AS id_row_local, 
					_status_source.label,
					_visit.visit_type, 
					_visit.fk_id
					FROM tbl_inspection_primary_visit AS _visit LEFT JOIN #cache_status_source AS _status_source ON _status_source.id = _visit.visit_type) AS _temp		
		WHERE _temp.id_row_local = 1


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
			_status.label AS status_label,
			--STUFF  ((SELECT DISTINCT ';' + CAST(visit_by AS VARCHAR(5))
            --                     FROM            tbl_inspection_primary_visit _visit
            --                     WHERE        _master.id_key = _visit.fk_id FOR XML PATH(''), TYPE, ROOT ).value('root[1]', 'varchar(max)'), 1, 1, '') AS visit_by_list,
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
			LEFT JOIN
					#sub_status AS _status ON _status.fk_id = _master.id_key
			-- Visit by filter.
				INNER JOIN
					#filter_visit AS _filter_visit ON _master.id = _filter_visit.id

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
							CASE WHEN @param_sort_field = 4 AND @param_sort_order = 1	THEN visit_by_list_label	END DESC,
							CASE WHEN @param_sort_field = 5 AND @param_sort_order = 0	THEN status_label	END ASC,
							CASE WHEN @param_sort_field = 5 AND @param_sort_order = 1	THEN status_label	END DESC) AS id_row_local, 
		*
		INTO #cache_primary
		FROM #cache_peliminary

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows
	
