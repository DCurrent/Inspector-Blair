USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[master_delete]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[master_delete]
	
	-- Parameters
	@id				bigint		= NULL,	-- Primary key.
	@update_by		bigint		= NULL,	-- ID of account performing delete.
	@update_ip		varchar(50)	= NULL	-- IP address of account performing delete.
AS	
BEGIN
	
	SET NOCOUNT ON;	
	
		-- Create cache table to hold results.
		CREATE TABLE #cache_result
		(
			id		bigint
		) 

		-- Simple update - mark the target record as inactive.
		UPDATE tbl_master 
			SET active = 0		
		OUTPUT INSERTED.id INTO #cache_result
		WHERE id = @id		

		-- Output updated ID.
		SELECT id FROM #cache_result;
					
END

GO
