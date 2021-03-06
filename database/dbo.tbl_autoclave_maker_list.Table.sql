USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_autoclave_maker_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_autoclave_maker_list](
	[id] [uniqueidentifier] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[record_deleted] [bit] NULL,
 CONSTRAINT [PK_tbl_autoclave_maker] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_autoclave_maker_list] ADD  CONSTRAINT [DF_tbl_autoclave_maker_guid_id]  DEFAULT (newid()) FOR [id]
GO
ALTER TABLE [dbo].[tbl_autoclave_maker_list] ADD  CONSTRAINT [DF_tbl_autoclave_maker_record_deleted]  DEFAULT ((0)) FOR [record_deleted]
GO
