USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[audit_question_category_list_for_inspection_entry]    Script Date: 2017-04-05 23:52:44 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[audit_question_category_list_for_inspection_entry]
	
	-- Parameters
	
	-- Paging
	@param_page_current			int = -1,
	@param_page_rows			int = NULL,

	-- Filter
	@param_filter_inclusion		int	= NULL
	
AS	
	SET NOCOUNT ON;
		
		-- Get a cache of the source table of possible
		-- selections ready with master table information 
		-- included. This will be combined when we are
		-- finished to aquire detailed information
		-- about the filtered selections.
			SELECT DISTINCT
				_master.id, 
				_master.id_key,
				_main.label
			INTO #cache_category_source
			FROM dbo.tbl_audit_question_category_source _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 
			WHERE _master.active = 1

		-- Now get the primary table with master table info.
		-- Then and use a right join to combine the sub table
		-- that is populated with selections from our source table above.
		-- Finally, use a sub query to filter for the sub items.
			SELECT DISTINCT
				_category.item
			INTO #cache_question
			FROM dbo.tbl_audit_question _main			
				JOIN tbl_master _master ON _main.id_key = _master.id_key 
				RIGHT OUTER JOIN
                         tbl_audit_question_category AS _category ON _master.id = _category.fk_id
			WHERE _master.active = 1 
				AND 
					(
						Exists (
									SELECT 1
									FROM dbo.tbl_audit_question_inclusion AS _inclusion
									WHERE _inclusion.fk_id = _master.id_key
									AND _inclusion.item IN (@param_filter_inclusion)
								) OR (@param_filter_inclusion = NULL OR @param_filter_inclusion = -1)
					) 

			-- Combine filtered selections with source table so we can 
			-- output whatever details are needed.
			SELECT DISTINCT
				_main.id,
				_main.id_key,
				_main.label
			INTO #cache_primary 
			FROM #cache_category_source _main 
			JOIN #cache_question _question ON _main.id = _question.item
			ORDER BY label

	-- Execute paging SP to output paged records and control data.
		EXEC master_paging
				@param_page_current,
				@param_page_rows

	
	
