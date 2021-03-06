USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_account_role]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_account_role](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NOT NULL,
	[item] [int] NULL,
 CONSTRAINT [PK_tbl_account_role] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_account_role]  WITH CHECK ADD  CONSTRAINT [FK_tbl_account_role] FOREIGN KEY([fk_id])
REFERENCES [dbo].[tbl_account] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_account_role] CHECK CONSTRAINT [FK_tbl_account_role]
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tbl_account one to many tbl_account_role.' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'tbl_account_role', @level2type=N'CONSTRAINT',@level2name=N'FK_tbl_account_role'
GO
