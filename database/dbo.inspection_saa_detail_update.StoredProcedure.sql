USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_saa_detail_update]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-06-21
-- Description:	Insert or update sub items.
-- =============================================
CREATE PROCEDURE [dbo].[inspection_saa_detail_update]
	
	-- Parameters
	@fk_id			uniqueidentifier	= '00000000-0000-0000-0000-000000000000',
	@xml			xml					= NULL

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	-- Create temp table to cache update.		
	CREATE TABLE #cache_update
	(
		id				uniqueidentifier,
		label			varchar(50),
		details			varchar(max),
		correction		uniqueidentifier,
		complete		bit
	)	
	
	-- Populate temp table with values from XML string.
	INSERT INTO #cache_update (id, 
							label, 
							details,
							correction,
							complete)
	(SELECT 
    row.value('@id',				'UNIQUEIDENTIFIER')	AS id,
    row.value('label[1]',			'VARCHAR(10)')		AS label,
    row.value('details[1]',			'VARCHAR(max)')		AS details,
    row.value('correction[1]',		'UNIQUEIDENTIFIER')	AS correction,
    row.value('complete[1]',		'BIT')				AS complete

    FROM @xml.nodes('root/row')Catalog(row))
	
	-- Delete entries that are matched to foreign key but not in temp table. 
	UPDATE tbl_inspection_saa_detail
		SET			
			record_deleted	= 1 
		WHERE fk_id = @fk_id AND id NOT IN(SELECT id FROM #cache_update)
	
	-- Now perform a merge query to update or insert rows as needed.
	MERGE INTO tbl_inspection_saa_detail AS _target
		USING #cache_update AS _source
			ON
				_target.id =  _source.id		
		
		-- If an ID match is found we will udate the matched row
		-- but only if the data differs from what is already present and there is
		-- actually information to add. 
		WHEN MATCHED		AND	
								(_source.correction != '00000000-0000-0000-0000-000000000000')  
							AND 
								(_target.details		!= _source.details 
								OR _source.correction	!= _target.correction
								OR _source.complete		!= _target.complete) THEN
			UPDATE 
				SET										
					label 			= _source.label,
					details			= _source.details,					
					correction		= _source.correction,
					complete		= _source.complete
					
					
					
		-- If no ID match is found then we insert a new
		-- row to the table, assuming there is data
		-- to insert.	
		WHEN NOT MATCHED THEN
			INSERT (fk_id,					
					label,
					details,
					correction,
					complete)
			
			VALUES (@fk_id,					
					_source.label, 
					_source.details,
					_source.correction,
					_source.complete);		
					
END

GO
