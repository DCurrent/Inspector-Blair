USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_audit_question_reference]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_audit_question_reference](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
 CONSTRAINT [PK_tbl_audit_question_reference] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
