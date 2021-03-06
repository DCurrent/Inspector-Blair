USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_config_form]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_config_form](
	[id_key] [int] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[title] [varchar](50) NULL,
	[description] [varchar](max) NULL,
	[main_sql_name] [varchar](50) NULL,
	[main_object_name] [varchar](50) NULL,
	[slug] [varchar](50) NULL,
	[file_name] [varchar](50) NULL,
	[inspection] [bit] NULL,
 CONSTRAINT [PK_tbl_settings_form] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_config_form]  WITH CHECK ADD  CONSTRAINT [FK_tbl_settings_form_tbl_master] FOREIGN KEY([id_key])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_config_form] CHECK CONSTRAINT [FK_tbl_settings_form_tbl_master]
GO
