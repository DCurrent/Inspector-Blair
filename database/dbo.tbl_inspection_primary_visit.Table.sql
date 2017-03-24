USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_inspection_primary_visit]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_inspection_primary_visit](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[visit_type] [int] NULL,
	[visit_by] [int] NULL,
	[time_recorded] [datetime2](7) NULL,
 CONSTRAINT [PK__tbl_insp__3213E83F40058253] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
