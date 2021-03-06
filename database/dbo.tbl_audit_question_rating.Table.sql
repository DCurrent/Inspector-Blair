USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_audit_question_rating]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_audit_question_rating](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NULL,
	[item] [int] NULL,
 CONSTRAINT [PK_tbl_audit_question_rating] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_audit_question_rating]  WITH CHECK ADD  CONSTRAINT [FK_tbl_audit_question_rating_tbl_audit_question] FOREIGN KEY([fk_id])
REFERENCES [dbo].[tbl_audit_question] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_audit_question_rating] CHECK CONSTRAINT [FK_tbl_audit_question_rating_tbl_audit_question]
GO
