USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[master_delete]    Script Date: 2017-04-12 16:32:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[master_delete]
	
	-- Parameters
	@param_id				int			= NULL,	-- Primary key.
	@param_update_by		int			= NULL,	-- ID of account performing delete.
	@param_update_host		varchar(50)	= NULL	-- IP address of account performing delete.
AS	
BEGIN
	
	SET NOCOUNT ON;	
	
		-- Create cache table to hold results.
		CREATE TABLE #cache_result
		(
			id		int
		) 

		-- Simple update - mark the target records matching ID as inactive.
		UPDATE tbl_master 
			SET active = 0,
			update_host = @param_update_host,
			update_by	= @param_update_by,
			update_time = GETDATE()		
		OUTPUT INSERTED.id INTO #cache_result
		WHERE id = @param_id		

		-- Output updated ID.
		SELECT id FROM #cache_result;
					
END
