USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_config_main_navigation]    Script Date: 2017-03-23 20:37:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_config_main_navigation](
	[id_key] [int] NOT NULL,
	[item] [int] NULL,
	[parent] [int] NULL,
	[label] [varchar](25) NULL,
	[details] [varchar](max) NULL,
	[type] [int] NULL,
 CONSTRAINT [PK_config_main_navigation] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_config_main_navigation]  WITH CHECK ADD  CONSTRAINT [FK_config_navigation_main_tbl_master] FOREIGN KEY([id_key])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_config_main_navigation] CHECK CONSTRAINT [FK_config_navigation_main_tbl_master]
GO
