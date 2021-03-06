USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_area_usage]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_area_usage](
	[id] [int] NOT NULL,
	[id_perm] [uniqueidentifier] NOT NULL,
	[id_parent] [bigint] NOT NULL,
	[item] [bigint] NULL,
 CONSTRAINT [PK_tbl_area_use] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_area_usage]  WITH CHECK ADD  CONSTRAINT [FK_tbl_area_usage_tbl_master] FOREIGN KEY([id])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_area_usage] CHECK CONSTRAINT [FK_tbl_area_usage_tbl_master]
GO
