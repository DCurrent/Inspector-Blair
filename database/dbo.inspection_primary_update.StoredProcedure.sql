USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary_update]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
CREATE PROCEDURE [dbo].[inspection_primary_update]
	
	-- Parameters
	@id					uniqueidentifier	= '00000000-0000-0000-0000-000000000000',		-- Primary key. 
	@update_by			uniqueidentifier	= NULL,
	@update_ip			varchar(50)			= '',
	@label				varchar(50)			= '',
	@details			varchar(max)		= '',	
	@room_code			varchar(6)			= NULL,
	@inspection_type	uniqueidentifier	= NULL,
	@sub_party_xml		xml					= NULL,
	@sub_visit_xml		xml					= NULL
			
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	
	
	-- Final result of query to output or
	-- use by loggin, paging, etc.
	CREATE TABLE #cache_result
	(
		id	uniqueidentifier
	)

	-- ID from cache_result.
	DECLARE @update_id AS uniqueidentifier = NULL
	 
	MERGE INTO tbl_inspection_primary AS _update_target
		USING
			(SELECT @id as _search_col) AS _search
			ON
				_update_target.id =  _search._search_col		
		
		-- If an ID match is found we will udate the matched row
		-- but only if the data differs from what is already present. 
		WHEN MATCHED THEN
			UPDATE 
				SET					
					label 				= @label,
					details				= @details,						
					room_code			= @room_code,
					inspection_type		= @inspection_type
							
		
		-- If no ID match is found then we insert a new
		-- row to the table.	
		WHEN NOT MATCHED THEN
			INSERT (label, 
					details,
					room_code,
					inspection_type)
			
			VALUES (@label, 
					@details,
					@room_code,
					@inspection_type)
		
		-- Output the primary key of newly created or updated row.
		OUTPUT INSERTED.id INTO #cache_result;

		-- Populate update_id with the latest ID.
		SET @update_id		= (SELECT id FROM #cache_result)

		-- Populate sub table (party)
		
		-- Set up temp table to cache values from xml string.				
		CREATE TABLE #cache_sub_party
		(
			id				uniqueidentifier,
			item			uniqueidentifier
		)	
	
		-- Populate temp table with values from XML string.
		INSERT INTO #cache_sub_party (id, 
									item)
		(SELECT 
			row.value('@id',				'UNIQUEIDENTIFIER')		AS id,
			row.value('party[1]',			'UNIQUEIDENTIFIER')		AS item

		FROM @sub_party_xml.nodes('root/row')Catalog(row))
	
		-- Delete sub entries that are matched to foreign key but not in temp table. 
		UPDATE tbl_inspection_primary_party
			SET				
				record_deleted	= 1 
			WHERE fk_id = @update_id AND id NOT IN(SELECT id FROM #cache_sub_party)
	
		-- Now perform a merge query to update or insert rows as needed.
		MERGE INTO tbl_inspection_primary_party AS _target
			USING #cache_sub_party AS _source
				ON
					_target.id =  _source.id		
		
			-- If an ID match is found we will udate the matched row
			-- but only if the data differs from what is already present. 
			WHEN MATCHED AND (_target.party	!= _source.item) THEN
					
				UPDATE 
					SET						
						party			= _source.item
					
			-- If no ID match is found then we insert a new
			-- row to the table.	
			WHEN NOT MATCHED THEN
				INSERT (fk_id,
						party)
			
				VALUES (@update_id,
						_source.item);
		
		-- Populate sub table (visit)

		-- Set up temp table to cache values from xml string.				
		CREATE TABLE #cache_sub_visit
		(
			id				uniqueidentifier,
			visit_type		uniqueidentifier,
			time_recorded	datetime2,
			item			uniqueidentifier
		)	
	
		-- Populate temp table with values from XML string.
		INSERT INTO #cache_sub_visit (id, 
									visit_type,
									time_recorded,
									item)
		(SELECT 
			row.value('@id',				'UNIQUEIDENTIFIER')			AS id,
			row.value('visit_type[1]',		'UNIQUEIDENTIFIER')			AS visit_type,
			row.value('time_recorded[1]',	'datetime2')				AS time_recorded,
			row.value('visit_by[1]',		'UNIQUEIDENTIFIER')			AS item

		FROM @sub_visit_xml.nodes('root/row')Catalog(row))
	
		-- Delete sub entries that are matched to foreign key but not in temp table. 
		UPDATE tbl_inspection_primary_visit
			SET				
				record_deleted	= 1 
			WHERE fk_id = @update_id AND id NOT IN(SELECT id FROM #cache_sub_visit)
	
		-- Now perform a merge query to update or insert rows as needed.
		MERGE INTO tbl_inspection_primary_visit AS _target
			USING #cache_sub_visit AS _source
				ON
					_target.id =  _source.id		
		
			-- If an ID match is found we will udate the matched row
			-- but only if the data differs from what is already present. 
			WHEN MATCHED AND (_target.visit_type != _source.visit_type 
							OR _target.visit_by != _source.item 
							OR _source.time_recorded != _target.time_recorded) THEN
					
				UPDATE 
					SET						
						visit_type		= _source.visit_type,
						visit_by		= _source.item,
						time_recorded	= _source.time_recorded
					
			-- If no ID match is found then we insert a new
			-- row to the table.	
			WHEN NOT MATCHED THEN
				INSERT (fk_id,
						visit_type,
						visit_by,
						time_recorded)
			
				VALUES (@update_id,
						_source.visit_type,
						_source.item,
						_source.time_recorded);

		-- Populate update log.
		EXEC log_insert
				@update_by,
				@update_ip

		-- Output updated ID.
		SELECT id FROM #cache_result;
					
END

GO
