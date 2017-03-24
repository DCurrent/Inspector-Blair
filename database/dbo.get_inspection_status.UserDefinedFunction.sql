USE [inspection]
GO
/****** Object:  UserDefinedFunction [dbo].[get_inspection_status]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[get_inspection_status] (@id uniqueidentifier) 
RETURNS uniqueidentifier
AS
BEGIN

DECLARE @result   uniqueidentifier -- Final result.

SET @result = (SELECT TOP 1 visit_type FROM tbl_inspection_primary_visit WHERE fk_id = @id ORDER BY time_recorded DESC)


RETURN @result

END

GO
