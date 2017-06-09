USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[audit_question]    Script Date: 6/9/2017 5:03:33 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single record detail.
-- =============================================

ALTER PROCEDURE [dbo].[audit_question]
	
	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL
		
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
				_master.create_time,
				_master.update_time,
				_main.label, 
				_main.details,
				_main.finding,
				_main.corrective_action,
				_main.status,
				_master.active	
		INTO #cache_primary					
		FROM dbo.tbl_audit_question AS _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key 
		WHERE
			-- Normal filter. This produces an active 
			-- revision list of all records.
			(@param_filter_id_key IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key)			
		ORDER BY _main.label, _main.finding, _main.corrective_action
			
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

			-- Category
				-- We'll need to tie the subsets to 
				-- their respective source tables. To 
				-- do that we need the list of source
				-- items as a complete record set
				-- joined to master table.
				SELECT				 
					_main.details, 
					_master.id,
					_master.id_key, 
					_main.label  
				INTO #cache_item_list_category
				FROM tbl_audit_question_category_source AS _main 
					JOIN 
						tbl_master AS _master ON _master.id_key = _main.id_key 
				WHERE _master.active = 1

				-- Now get the subset records for the main
				-- record by matching main record's key id
				-- to subset's foreign key. Finally, we tie
				-- in the source list record set via the
				-- item field.
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_list.label,
					_list.details
				FROM tbl_audit_question_category _main    
					LEFT JOIN
						-- Join the subset's source table
						-- to get complete data (label, detail, etc.).
						#cache_item_list_category AS _list ON _list.id = _main.item  
				-- Filter to get only sub records related to main record set.
				WHERE _main.fk_id = @id_key

			-- Inclusion
				SELECT				 
					_main.details, 
					_master.id,
					_master.id_key, 
					_main.label  
				INTO #cache_item_list_inclusion 
				FROM tbl_config_form AS _main 
					JOIN 
						tbl_master AS _master ON _master.id_key = _main.id_key 
				WHERE _master.active = 1
				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_list.label,
					_list.details
				FROM tbl_audit_question_inclusion _main    
					LEFT JOIN						
						#cache_item_list_inclusion AS _list ON _list.id = _main.item 
				WHERE _main.fk_id = @id_key

			-- Rating
				SELECT				 
					_main.details, 
					_master.id,
					_master.id_key, 
					_main.label  
				INTO #cache_item_list_rating
				FROM tbl_audit_question_rating_source AS _main 
					JOIN 
						tbl_master AS _master ON _master.id_key = _main.id_key 
				WHERE _master.active = 1
				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item,
					_list.label,
					_list.details
				FROM tbl_audit_question_rating _main    
					LEFT JOIN						
						#cache_item_list_rating AS _list ON _list.id = _main.item 
				WHERE _main.fk_id = @id_key

			-- Reference				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.label,
					_main.details
				FROM tbl_audit_question_reference _main
				WHERE _main.fk_id = @id_key