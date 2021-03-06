USE [inspection]
GO
/****** Object:  UserDefinedFunction [dbo].[get_inspection_status]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[get_inspection_status] (@id int) 
RETURNS int
AS
BEGIN

DECLARE @result   int -- Final result.

SET @result = (SELECT TOP 1 _main.visit_type FROM tbl_inspection_primary_visit _main 
				JOIN tbl_master _master ON _main.id_key = _master.id_key
				WHERE fk_id = @id
				ORDER BY _master.create_time DESC)


RETURN @result

END

GO
