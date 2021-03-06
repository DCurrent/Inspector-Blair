USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[session_destroy]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-05-23
-- Description:	Destroy session data.
-- =============================================
CREATE PROCEDURE [dbo].[session_destroy]
	
	-- Parameters
	@id				varchar(40) = NULL	-- Primary key.

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		DELETE FROM dbo.tbl_session WHERE session_id = @id
					
END

GO
