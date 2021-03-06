USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[audit_question_update]    Script Date: 6/13/2017 9:52:15 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Insert or update items.
-- =============================================
ALTER PROCEDURE [dbo].[audit_question_update]
	
	-- Parameters
	@param_id_list			xml				= NULL, 
	@param_update_by		int				= NULL,
	@param_update_host		varchar(50)		= NULL,
	@param_label			varchar(25)		= NULL,
	@param_details			varchar(max)	= NULL,
	@param_finding				varchar(max)		= NULL,
	@param_corrective_action	varchar(max)		= NULL,
	@param_category			xml				= NULL,
	@param_inclusion		xml				= NULL,
	@param_rating			xml				= NULL,
	@param_reference		xml				= NULL,
	@param_status			smallint			= NULL
			

AS
BEGIN
	
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;	

	-- Local cache of master result.
	CREATE TABLE #cache_audit_question_update
	(
		id_row	int,
		id_key	int,
		id		int
	)

	-- Update master table. This creates a new
	-- version entry and primary key we need.
		INSERT INTO #cache_audit_question_update
			EXEC master_update
				@param_id_list,
				@param_update_by,
				@param_update_host

	-- Update data table using the
	-- new primary key from master table.
		INSERT INTO tbl_audit_question
				(id_key,
				label, 
				details,
				finding,
				corrective_action,
				status)	

		SELECT _master.id_key,
				@param_label, 
				@param_details,
				@param_finding,
				@param_corrective_action,
				@param_status
		FROM 
			#cache_audit_question_update AS _master
		
		-- Sub records

			DECLARE @temp_xml xml = NULL

			-- Declare and set a foreign key. Sub records
			-- are keyed by the the main record's key ID, NOT 
			-- the group ID.
			DECLARE @fk_id int = NULL

			SET @fk_id = (SELECT TOP 1 id_key FROM #cache_audit_question_update)
			
			-- Category
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
						INTO #cache_audit_question_category
						FROM @param_category.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_audit_question_category
							(fk_id, 
							item)
						SELECT @fk_id, 
								_source.item
						FROM #cache_audit_question_category _source
			
			--Inclusion
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
						INTO #cache_audit_question_inclusion
						FROM @param_inclusion.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_audit_question_inclusion
							(fk_id, 
							item)
						SELECT @fk_id, 
								_source.item
						FROM #cache_audit_question_inclusion _source

			--Rating
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT ROW_NUMBER() OVER(ORDER BY y) AS id_row, x.y.value('.','int') AS item	
						INTO #cache_audit_question_rating
						FROM @param_rating.nodes('root/row/@id') AS x(y) 					

					INSERT INTO tbl_audit_question_rating
							(fk_id, 
							item)
						SELECT @fk_id, 
								_source.item
						FROM #cache_audit_question_rating _source

			--Reference
				-- Get the list of records to update and 
				-- push them into a local temp table.
					SELECT 
					row.value('@id',				'INT')	AS id,
					row.value('details[1]',			'VARCHAR(max)')		AS details
						INTO #cache_audit_question_reference
					FROM @param_reference.nodes('root/row')Catalog(row)									

					INSERT INTO tbl_audit_question_reference
							(fk_id, 
							details)
						SELECT @fk_id, 
								_source.details
						FROM #cache_audit_question_reference _source

		-- Output ID of the newly inserted record.
		SELECT TOP 1
			_master.id
			FROM #cache_audit_question_update AS _main
			JOIN tbl_master AS _master ON _main.id_key = _master.id_key
			
					
END
