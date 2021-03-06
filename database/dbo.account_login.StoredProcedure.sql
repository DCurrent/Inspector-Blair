USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[account_login]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-05-22
-- Description:	Return session data.
-- =============================================
CREATE PROCEDURE [dbo].[account_login]
	
	-- Parameters
	@account			varchar(50) = NULL,
	@credential			varchar(50) = NULL

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		SELECT account 
			FROM dbo.tbl_account _main
			WHERE 
				_main.account = @account AND (_main.password IS NOT NULL AND _main.password  = @credential)
					
END

GO
