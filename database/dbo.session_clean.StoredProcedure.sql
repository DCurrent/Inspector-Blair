USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[session_clean]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-05-23
-- Description:	Remove all expired session data.
-- =============================================
CREATE PROCEDURE [dbo].[session_clean]
	
	-- Parameters
	@life_max	int = 1440	-- Maximum lifetime of a session in seconds.

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		DELETE FROM dbo.tbl_session WHERE (DATEDIFF(SECOND, last_update, GETDATE()) > @life_max)
					
END

GO
