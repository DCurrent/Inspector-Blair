USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_saa_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get list of items, ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_saa_list]
	
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

	
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
			SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.								
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 0	THEN _main.label			END ASC,
								CASE WHEN @param_sort_field = 1 AND @param_sort_order = 1	THEN _main.label			END DESC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 0	THEN _master.create_time	END ASC,
								CASE WHEN @param_sort_field = 2 AND @param_sort_order = 1	THEN _master.create_time	END DESC) AS id_row_local,
				_master.id, 
				_master.id_key,
				_main.label,
				_main.details,
				_main.type,
				_master.create_time
			INTO #cache_primary
			FROM dbo.tbl_inspection_primary _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 

				LEFT JOIN
					UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
				LEFT JOIN
					UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode         
				

			WHERE _master.active = 1 AND ((_master.create_time BETWEEN @param_date_start AND @param_date_end) OR @param_date_start IS NULL OR @param_date_end IS NULL)

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows
	

GO
