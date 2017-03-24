USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_question_list_select]    Script Date: 2017-03-23 20:37:04 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Created: 2015-11-16
-- Description: Return list of items, sorted, and unpaged.
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

CREATE PROCEDURE [dbo].[inspection_question_list_select]

	-- Parameters
	@category	uniqueidentifier	= NULL,
	@inclusion	uniqueidentifier	= NULL
	
AS
	
	SET NOCOUNT ON
	
	-- Set up table var so we can reuse results if needed.
	CREATE TABLE #cache_primary
	(
		row_id		int,
		id			uniqueidentifier,
		label		varchar(255),
		details		varchar(max),
		finding		varchar(max),
		corrective_action varchar(max)
	)
	
	-- Populate main table var. This is the primary query. order
	-- and filter should be placed here if possible.
	INSERT INTO #cache_primary (row_id,
							id,
							label,
							details,
							finding,
							corrective_action)
		(SELECT ROW_NUMBER() OVER(ORDER BY _main.finding)
			AS _row_number,
				_main.id,
				_main.label,
				_main.details,
				_main.finding,
				_main.corrective_action
			FROM dbo.tbl_audit_question _main
			
			WHERE (_main.record_deleted IS NULL OR _main.record_deleted = 0) 
				
				-- Categories are stored in a sub table, so we'll need to look for their exisitence 
				-- in that table here.
				AND (Exists(
					SELECT 1
					From dbo.tbl_audit_question_category As _category
					WHERE _category.fk_id = _main.id 
						AND (_category.record_deleted IS NULL OR _category.record_deleted = 0)
						AND ((_category.item_id IN (@category)) OR (@category IS NULL OR @category = '00000000-0000-0000-0000-000000000000'))))

				-- Inclusions are stored in a sub table, so we'll need to look for their exisitence 
				-- in that table here.
				AND (Exists(
					Select 1
					From dbo.tbl_audit_question_inclusion As _inclusion_filter
					Where _inclusion_filter.fk_id = _main.id
						AND (_inclusion_filter.record_deleted IS NULL OR _inclusion_filter.record_deleted = 0)
						AND ((_inclusion_filter.item_id IN (@inclusion)) OR (@inclusion IS NULL OR @inclusion = '00000000-0000-0000-0000-000000000000')))))
			
	-- Now select from temp table.
	SELECT * 
	FROM #cache_primary
	
	
		
GO
