USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[session_get]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-05-22
-- Description:	Return session data.
-- =============================================
CREATE PROCEDURE [dbo].[session_get]
	
	-- Parameters
	@id				varchar(40) = NULL	-- Primary key.

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		SELECT session_data 
			FROM dbo.tbl_session
			WHERE 
				session_id = @id
					
END

GO
