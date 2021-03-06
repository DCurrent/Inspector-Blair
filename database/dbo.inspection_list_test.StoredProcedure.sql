USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_list_test]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_list_test]

-- Parameters
	
	-- paging
	@page_current		int				= 1,
	@page_rows			int				= 10,	
	@page_last			float			OUTPUT,
	@row_count_total	int				OUTPUT,	
	
	-- filter
	@inspector			int				= NULL,
	@create_from		datetime2		= NULL,
	@create_to			datetime2		= NULL,
	@update_from		datetime2		= NULL,
	@update_to			datetime2		= NULL,
	@status				tinyint			= NULL,
	@building			varchar(4)		= NULL,
	
	-- sorting
	@sort_field			tinyint 		OUTPUT,
	@sort_order			bit				OUTPUT	
	
AS	

	-- We don't want rows affected to be returned with
	-- results. Turn it off here.
	SET NOCOUNT ON;

	-- Working vars
	DECLARE 
		@visit			AS NVARCHAR(50),
		@dyn_query		AS NVARCHAR(500)

-- This is the primary table we will get data and act on. We also need an temporary holding table.
create table #temp_main
(
	row						int,
	id						int,		
	status					tinyint,
	status_label			varchar(50),
	room_code					varchar(10),
	room_code				varchar(6),
	building_code			varchar(4),
	building_name			varchar(20),	
	label					varchar(50),				
	log_create				datetime2,
	log_update				datetime2,
	inspection_type			smallint,
	inspection_type_label	varchar(50)				
)

create table #temp_cache
(
	row						int,
	id						int,		
	status					tinyint,
	status_label			varchar(50),
	room_code					varchar(10),
	room_code				varchar(6),
	building_code			varchar(4),
	building_name			varchar(20),	
	label					varchar(50),				
	log_create				datetime2,
	log_update				datetime2,
	inspection_type			smallint,
	inspection_type_label	varchar(50)				
)

-- For readability and maintenance, we will first populate our main temporary table with a query 
-- filtered by any non-dynamic parameters in use. Afterward, we will break each dynamic parameter 
-- down into a separate query that we’ll run against the main temporary table. Then we can page 
-- the final results and output to user. 

-- Populate primary temp table with non dynamic query.
INSERT INTO #temp_main (row, 
							id, 
							status, 
							status_label, 
							room_code, 
							room_code, 
							building_code, 
							building_name, 
							label, 
							log_create, 
							log_update, 
							inspection_type,
							inspection_type_label)
	(SELECT ROW_NUMBER() OVER(ORDER BY _main.log_create)			
	
		AS _row_number,
			_main.id, 
			_main.status,
			_status.label,			
			_room.RoomID,
			_main.room_code,
			_building.BuildingCode,
			_building.BuildingName,
			_main.label,
			_main.log_create,
			_main.log_update,
			_type.id,
			_type.label
	FROM dbo.tbl_inspection_primary _main LEFT JOIN
                      UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
                      LEFT JOIN
                      UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode                      
                      LEFT JOIN
                      dbo.tbl_inspection_status_list AS _status ON _main.status = _status.id
						LEFT JOIN
                      dbo.tbl_inspection_type_list AS _type ON _main.inspection_type = _type.id  
                       
	WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0)			
			AND (_main.log_create >= @create_from	OR @create_from IS NULL OR @create_from = '') 
			AND (_main.log_create <= @create_to		OR @create_to	IS NULL OR @create_to = '')
			AND (_main.log_update >= @update_from	OR @update_from IS NULL OR @update_from = '') 
			AND (_main.log_update <= @update_to		OR @update_to	IS NULL OR @update_to = '')
			AND (_main.status		= @status		OR @status		IS NULL OR @status = '')
			AND (_building.BuildingCode		= @building		OR @building		IS NULL OR @building = '-1'))


SET @visit = '0, 1, 2, 10, 13, 14, 15, 17, 18, 21, 22, 23, 25, 29, 31, 35, 36, 38' 

-- If the user provided any filter values we'll apply this
-- dynamic query to our primary temp table. This may be a
-- complicated query in its own right depending on the
-- filter needs.
IF(@visit <> '')
        BEGIN
			-- Create the dynamic query.
            SET @dyn_query = 'SELECT * FROM #temp_main _main
				WHERE Exists(
								Select 1
								From dbo.tbl_inspection_primary_visit As _visit
								Where _visit.fk_id = _main.id				
									And _visit.visit_by IN(' + @visit + ')'

				-- Execute the dynamic query and insert results into temp cache.
				INSERT INTO #temp_cache EXEC sp_executesql @dyn_query

				-- Replace records in primary temp table by first clearing the
				-- main temp table and and then inserting rows from the temp 
				-- cache. We then clear the cache for next use.
				DELETE FROM #temp_main
				INSERT INTO #temp_main SELECT * FROM #temp_cache 
				DELETE FROM #temp_cache
        END

SELECT * FROM #temp_main
drop table #temp_main


GO
