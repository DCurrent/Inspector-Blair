USE [inspection]
GO
/****** Object:  UserDefinedFunction [dbo].[get_log_update_time]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[get_log_update_time] (@id uniqueidentifier) 
RETURNS datetime2
AS
BEGIN

DECLARE @result   datetime2 -- Final result.

SET @result = (SELECT TOP 1 update_time FROM tbl_log_update WHERE update_id = @id ORDER BY update_time DESC)


RETURN @result

END

GO
