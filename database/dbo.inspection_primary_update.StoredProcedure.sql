USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary_update]    Script Date: 2017-07-31 15:46:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
ALTER PROCEDURE [dbo].[inspection_primary_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(25)		= NULL,
	@param_details			varchar(max)	= NULL,
	@param_type				xml				= NULL,
	@param_area				xml				= NULL,
	@param_party			xml				= NULL,
	@param_visit			xml				= NULL,
	@param_detail			xml				= NULL

	
			

AS
BEGIN
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

	-- Local cache of master result.
	CREATE TABLE #cache_inspection_update
	(
		id_row	int,
		id_key	int,
		id		int
	)

	-- Update master table. This creates a new
	-- version entry and primary key we need.
		INSERT INTO #cache_inspection_update
			EXEC master_update
				@param_id_list,
				@param_update_by,
				@param_update_host

	-- Update data table using the
	-- new primary key from master table.
		INSERT INTO tbl_inspection_primary
				(id_key,
				label, 
				details)	

		SELECT _master.id_key,
				@param_label, 
				@param_details
		FROM 
			#cache_inspection_update AS _master
		
		-- Sub records

			DECLARE @temp_xml xml = NULL

			-- Declare and set a foreign key. Sub records
			-- are keyed by the the main record's key ID, NOT 
			-- the group ID.
			DECLARE @fk_id int = NULL

			SET @fk_id = (SELECT TOP 1 id_key FROM #cache_inspection_update)
			
			-- Type
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
						INTO #cache_inspection_type
						FROM @param_type.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_inspection_primary_type
							(fk_id, 
							item)
						SELECT @fk_id, 
								_source.item
						FROM #cache_inspection_type _source

			-- Area
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','char(6)') AS item	
						INTO #cache_inspection_area
						FROM @param_area.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_inspection_primary_area
							(fk_id, 
							code)
						SELECT @fk_id, 
								_source.item
						FROM #cache_inspection_area _source

			-- Parties
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
						INTO #cache_inspection_party
						FROM @param_party.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_inspection_primary_party
							(fk_id, 
							item)
						SELECT @fk_id, 
								_source.item
						FROM #cache_inspection_party _source

			-- Visits

				-- Populate temp table with values from XML string.
					
					SELECT 
						row.value('@id',				'INT')			AS id,
						row.value('label[1]',			'VARCHAR(10)')	AS label,
						row.value('details[1]',			'VARCHAR(max)') AS details,
						row.value('visit_type[1]',		'INT')			AS visit_type,
						row.value('time_recorded[1]',	'datetime2')	AS time_recorded,
						row.value('visit_by[1]',		'INT')			AS visit_by
					INTO #cache_inspection_visit
					FROM @param_visit.nodes('root/row')Catalog(row)

					INSERT INTO tbl_inspection_primary_visit
						(fk_id,
						visit_by,
						visit_type,
						time_recorded)
					SELECT @fk_id,
						_source.visit_by,
						_source.visit_type,
						_source.time_recorded
					FROM #cache_inspection_visit AS _source 

			-- Detail

				-- Populate temp table with values from XML string.
					
					SELECT 
						row.value('@id',				'INT')			AS id,
						row.value('label[1]',			'VARCHAR(10)')	AS label,
						row.value('details[1]',			'VARCHAR(max)') AS details,
						row.value('correction[1]',		'INT')			AS correction,
						row.value('complete[1]',		'bit')			AS complete
					INTO #cache_inspection_detail
					FROM @param_detail.nodes('root/row')Catalog(row)

					INSERT INTO tbl_inspection_primary_detail
						(fk_id,
						label,
						details,
						correction,
						complete)
					SELECT @fk_id,
						_source.label,
						_source.details,
						_source.correction,
						_source.complete
					FROM #cache_inspection_detail AS _source 

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_inspection_update AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END
