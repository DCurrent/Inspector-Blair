USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_primary]    Script Date: 2017-08-09 14:29:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single record detail.
-- =============================================

ALTER PROCEDURE [dbo].[inspection_primary]
	
	-- Sorting
	@sort_field				tinyint		= 0,
	@sort_order				bit			= 0,

	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL,
	@param_filter_type		int = NULL
		
AS	
	SET NOCOUNT ON;

	-- Create and populate the main data cache. This is 
	-- where we will do most (if not all) of our JOINs, 
	-- sorting and filtering to create a complete record set of
	-- primary data for consumption. We use a temporary
	-- table for performance and convenience. This temp table
	-- is also available in any other procedures we might call
	-- while this one is running. If we remember to use a 
	-- consistent naming convention, that will in turn allow us 
	-- to encapsulate a lot of repetitive work into reusable sub 
	-- procedures and keep their parameters to a bare minimum.		
		SELECT			
				_master.id,
				_master.id_key, 
				_master.active,
				_main.label, 
				_main.details,
				_master.create_time,
				_type.item AS type
		INTO #cache_primary					
		FROM dbo.tbl_inspection_primary AS _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key
			JOIN tbl_inspection_primary_type _type ON _type.fk_id = _master.id_key
		WHERE
			-- Normal filter. This produces an active 
			-- revision list of all records.
			((@param_filter_id_key IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key))
			
			AND _type.item = @param_filter_type			
		ORDER BY _main.label
			
	-- Navigation. This executes the navigation
	-- procedure, which produces a recordset
	-- including next ID, last ID, etc. for
	-- use by the control code to create record
	-- navigation buttons. See the stored 
	-- procedure for details.
		EXEC master_navigation @param_filter_id

	-- Select and output recordsets of data.

		-- Main (primary) data. We've already done all of
		-- the data processing. Just output the recordset
		-- filtered with ID.
		SELECT
			* 
		FROM 
			#cache_primary AS _data
		WHERE _data.id = @param_filter_id	
	
		-- Subsets. Once all the work is done for our primary table 
		-- and associated ancillary functionality, we can now output
		-- any sub data record sets. 
		
			-- First thing we need is the key id to
			-- relate to the the subsets foreign keys.
			-- We'll grab that from the finished main 
			-- record set and store it as a variable.
			DECLARE @id_key int = NULL
			SET @id_key = (SELECT TOP 1 id_key FROM #cache_primary WHERE id = @param_filter_id)

			-- Responsible parties	
				SELECT
					_master.id_key,
					_master.id,
					_main.name_f,
					_main.name_l
				INTO #cache_source_account
				FROM tbl_account _main
					JOIN tbl_master _master ON _main.id_key = _master.id_key
				WHERE _master.active = 1

				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_account.name_f,
					_account.name_l
				FROM tbl_inspection_primary_party _main 
					LEFT JOIN #cache_source_account _account ON _account.id = _main.item
				WHERE _main.fk_id = @id_key 

			-- Visit
				SELECT
					_master.id_key,
					_master.id,
					_main.label,
					_main.details
				INTO #cache_source_type
				FROM tbl_inspection_status_source _main
					JOIN tbl_master _master ON _main.id_key = _master.id_key
				WHERE _master.active = 1

				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.visit_by,
					_main.visit_type,
					_inspection_type_source.label AS visit_type_label,
					_account.name_f,
					_account.name_l,
					_main.time_recorded
				FROM tbl_inspection_primary_visit _main
					LEFT JOIN #cache_source_type AS _inspection_type_source ON _inspection_type_source.id = _main.visit_type
					LEFT JOIN #cache_source_account AS _account ON _account.id = _main.visit_by
				WHERE _main.fk_id = @id_key

			-- Details	
				SELECT
					_master.id_key,
					_master.id,
					_main.finding
				INTO #cache_source_audit_question
				FROM tbl_audit_question _main
					JOIN tbl_master _master ON _main.id_key = _master.id_key
				WHERE _master.active = 1 
						
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.label,
					_main.details,
					_main.correction,
					_main.complete,
					_inspection_audit_question.finding
				FROM tbl_inspection_primary_detail _main
				LEFT JOIN #cache_source_audit_question AS _inspection_audit_question ON _inspection_audit_question.id = _main.correction
				WHERE _main.fk_id = @id_key

			-- Area information.	
				-- Get the area code from inspection area
				-- sub table. Then we can use it to call
				-- the area survey SP by area code.			
					
					-- Initialize area code filter var.
					DECLARE @sub_area_filter_code int = NULL

					-- Populate code filter var with the 
					-- area code by querying the inspection
					-- area sub table by foreign key.
					-- This works because for now, there is
					-- only one area entry per inspection.
					SET @sub_area_filter_code = (SELECT
						_main.code									
					FROM tbl_inspection_primary_area AS _main
					WHERE _main.fk_id = @id_key)

					-- Execute the area survey SP and pass
					-- the area code as a filter argument.
					EXEC area_survey NULL, NULL, NULL, NULL, @sub_area_filter_code
				