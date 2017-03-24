USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[audit_question_category_list_for_inspection_entry]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2016-06-21
-- Description:	Get list of items, optionally ordered and paged.
-- =============================================

CREATE PROCEDURE [dbo].[audit_question_category_list_for_inspection_entry]
	
	-- Parameters
	@page_current		int				= 1,
	@page_rows			int				= 10,
	@inclusion			uniqueidentifier = NULL	
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		--filters
	
	-- Set up table var so we can reuse results.		
	CREATE TABLE #cache_temp
	(
		id			uniqueidentifier,
		log_update	datetime2,
		label		varchar(50),
		details		varchar(max)
	)	

	CREATE TABLE #cache_primary
	(
		row_id		int,
		id			uniqueidentifier,
		log_update	datetime2,
		label		varchar(50),
		details		varchar(max)
	)
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO #cache_temp (id, log_update, label, details)
	(SELECT DISTINCT
			_category_list.id, 
						dbo.get_log_update_time(_category_list.id),
						_category_list.label, 
						_category_list.details
				FROM            tbl_audit_question AS _question RIGHT OUTER JOIN
                         tbl_audit_question_category AS _category ON _question.id = _category.fk_id LEFT OUTER JOIN
                         tbl_audit_question_category_list _category_list ON _category.item_id = _category_list.id
						 WHERE	( 
									(
										Exists (
													Select 1
													From dbo.tbl_audit_question_inclusion AS _inclusion
													Where _inclusion.fk_id = _question.id
													AND _inclusion.item_id IN (@inclusion)
												) OR (@inclusion = NULL OR @inclusion = '00000000-0000-0000-0000-000000000000')
									) AND
						 
									(_question.record_deleted IS NULL OR _question.record_deleted = 0)
								) 
						
				)	
	
	INSERT INTO #cache_primary (row_id, id, log_update, label, details)
	(SELECT ROW_NUMBER() OVER(ORDER BY label) 
		AS _row_number,
			id, 
			log_update,
			label, 
			details
		FROM	#cache_temp)
	
	-- Execute paging SP to output paged records and control data.
	EXEC paging_basic
		@page_current,
		@page_rows	
	

GO
