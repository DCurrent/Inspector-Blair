USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_biowaste_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_biowaste_list](
	[id] [uniqueidentifier] ROWGUIDCOL  NOT NULL,
	[label] [varchar](50) NULL,
	[description] [varchar](max) NULL,
	[record_deleted] [bit] NULL,
 CONSTRAINT [PK_tbl_biowaste_list] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_biowaste_list] ADD  CONSTRAINT [DF_tbl_biowaste_list_id]  DEFAULT (newid()) FOR [id]
GO
ALTER TABLE [dbo].[tbl_biowaste_list] ADD  CONSTRAINT [DF__tbl_biowa__recor__797309D9]  DEFAULT (NULL) FOR [record_deleted]
GO
