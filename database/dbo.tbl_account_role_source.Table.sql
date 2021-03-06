USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_account_role_source]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_account_role_source](
	[id_key] [int] NOT NULL,
	[label] [varchar](50) NOT NULL,
	[details] [varchar](max) NOT NULL,
 CONSTRAINT [PK_tbl_account_role_list] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_account_role_source]  WITH CHECK ADD  CONSTRAINT [tbl_master_KEY_tbl_account_role_list] FOREIGN KEY([id_key])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_account_role_source] CHECK CONSTRAINT [tbl_master_KEY_tbl_account_role_list]
GO
