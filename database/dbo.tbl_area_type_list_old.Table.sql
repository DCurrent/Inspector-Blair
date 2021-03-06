USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_area_type_list_old]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_area_type_list_old](
	[guid] [uniqueidentifier] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
PRIMARY KEY CLUSTERED 
(
	[guid] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_area_type_list_old] ADD  DEFAULT (newid()) FOR [guid]
GO
