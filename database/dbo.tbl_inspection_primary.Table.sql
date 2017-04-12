USE [inspection]
GO

/****** Object:  Table [dbo].[tbl_inspection_primary_area]    Script Date: 2017-04-12 15:28:20 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[tbl_inspection_primary_area](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NULL,
	[code] [varchar](6) NULL,
 CONSTRAINT [PK__tbl_inspection_primary_fk_tbl_inspection_primary_area] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[tbl_inspection_primary_area]  WITH CHECK ADD  CONSTRAINT [FK_tbl_inspection_primary_area_tbl_inspection_primary] FOREIGN KEY([fk_id])
REFERENCES [dbo].[tbl_inspection_primary] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO

ALTER TABLE [dbo].[tbl_inspection_primary_area] CHECK CONSTRAINT [FK_tbl_inspection_primary_area_tbl_inspection_primary]
GO


