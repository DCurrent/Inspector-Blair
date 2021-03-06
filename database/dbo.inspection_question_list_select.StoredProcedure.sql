USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_question_list_select]    Script Date: 6/13/2017 10:05:42 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Created: 2015-11-16
-- Description: Return list of items, sorted, and unpaged.
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

ALTER PROCEDURE [dbo].[inspection_question_list_select]

	-- Parameters
	@param_category		int	= NULL,
	@param_inclusion	int	= NULL
	
AS
	
	SET NOCOUNT ON
	
	
	-- Populate main table var. This is the primary query. order
	-- and filter should be placed here if possible.
	
		SELECT 
				_master.id,
				_master.id_key,
				_main.label,
				_main.details,
				_main.finding,
				_main.corrective_action
		INTO #cache_primary
			FROM dbo.tbl_audit_question _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key 
			WHERE (_master.active = 1) 
				
				-- Active question filter.
				AND _main.status = 1			

				-- Categories are stored in a sub table, so we'll need to look for their exisitence 
				-- in that table here.
				AND (Exists(
					SELECT 1
					FROM dbo.tbl_audit_question_category As _category
					JOIN tbl_master _master ON _main.id_key = _master.id_key
					WHERE _category.fk_id = _master.id 
						AND (_master.active = 1)
						AND ((_category.item IN (@param_category)) OR (@param_category IS NULL OR @param_category = -1))))

				-- Inclusions are stored in a sub table, so we'll need to look for their exisitence 
				-- in that table here.
				AND (Exists(
					Select 1
					From dbo.tbl_audit_question_inclusion As _inclusion_filter
					JOIN tbl_master _master ON _main.id_key = _master.id_key
					Where _inclusion_filter.fk_id = _master.id
						AND (_master.active = 1)
						AND ((_inclusion_filter.item IN (@param_inclusion)) OR (@param_inclusion IS NULL OR @param_inclusion = -1))))
			
	-- Now select from temp table.
	SELECT * 
	FROM #cache_primary
	ORDER BY label
	
	
		