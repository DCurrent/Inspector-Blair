USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_inspection_autoclave_detail]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_inspection_autoclave_detail](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[maker] [uniqueidentifier] NULL,
	[serial] [varchar](50) NULL,
	[tag] [varchar](50) NULL,
	[model] [varchar](50) NULL,
	[biowaste] [int] NULL,
 CONSTRAINT [PK_tbl_autoclave_inspection] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
