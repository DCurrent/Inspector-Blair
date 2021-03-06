USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_autoclave_detail_update]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-12-16
-- Description:	Insert or update sub items.
-- =============================================
CREATE PROCEDURE [dbo].[inspection_autoclave_detail_update]
	
	-- Parameters
	@fk_id			int				= 0,
	@xml			xml				= NULL,			
	@log_update		datetime2		= getdate,
	@log_update_by	varchar(10)		= 'NA',
	@log_update_ip	varchar(50)		= ''

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	-- Set up table var so we can reuse results.		
	DECLARE @temp_table TABLE
	(
		id				int,
		label			varchar(50),
		details			varchar(max),
		biowaste		int,
		maker			int,
		model			varchar(50),
		serial			varchar(50),						
		tag				varchar(50)
	)	
	
	-- Populate temp table with values from XML string.
	INSERT INTO @temp_table (id, 
							label, 
							details,
							biowaste,
							maker,
							model,
							serial,
							tag)
	(SELECT 
    row.value('@id',				'INT')			AS id,
    row.value('label[1]',			'VARCHAR(10)')	AS label,
    row.value('details[1]',			'VARCHAR(max)') AS details,
    row.value('biowaste[1]',		'INT')			AS biowaste,
    row.value('maker[1]',			'INT')			AS maker,
    row.value('model[1]',			'VARCHAR(50)')	AS model,
    row.value('serial[1]',			'VARCHAR(50)')	AS serial,
    row.value('tag[1]',				'VARCHAR(50)')	AS tag

    FROM @xml.nodes('root/row')Catalog(row))
	
	-- Delete entries that are matched to foreign key but not in temp table. 
	UPDATE tbl_inspection_autoclave_detail
		SET 
			log_update		= @log_update,
			log_update_by	= @log_update_by,
			log_update_ip	= @log_update_ip,
			record_deleted	= 1 
		WHERE fk_id = @fk_id AND id NOT IN(SELECT id FROM @temp_table)
	
	-- Now perform a merge query to update or insert rows as needed.
	MERGE INTO tbl_inspection_autoclave_detail AS _target
		USING @temp_table AS _source
			ON
				_target.id =  _source.id		
		
		-- If an ID match is found we will udate the matched row
		-- but only if the data differs from what is already present. 
		WHEN MATCHED AND (_source.details != _target.details 
							OR _source.biowaste != _target.biowaste 
							OR _source.maker	!= _target.maker
							OR _source.model	!= _target.model
							OR _source.serial	!= _target.serial
							OR _source.tag		!= _target.tag) THEN
			UPDATE 
				SET					
					log_update		= @log_update,
					log_update_by	= @log_update_by,
					log_update_ip	= @log_update_ip,						
					label 			= _source.label,
					details			= _source.details,
					biowaste		= _source.biowaste,
					maker			= _source.maker,
					model			= _source.model,
					serial			= _source.serial,
					tag				= _source.tag
					
					
					
		-- If no ID match is found then we insert a new
		-- row to the table.	
		WHEN NOT MATCHED THEN
			INSERT (fk_id,
					log_create,
					log_create_by,
					log_create_by_ip,
					log_update,
					log_update_by,
					log_update_ip,
					label,
					details,
					biowaste,
					maker,
					model,
					serial,
					tag)
			
			VALUES (@fk_id,
					@log_update,
					@log_update_by,
					@log_update_ip,					
					@log_update,
					@log_update_by,
					@log_update_ip,
					_source.label, 
					_source.details,
					_source.biowaste,
					_source.maker,
					_source.model,
					_source.serial,
					_source.tag);		
					
END

GO
