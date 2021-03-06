USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_account]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_account](
	[id_key] [int] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[account] [varchar](10) NULL,
	[password] [varchar](50) NULL,
	[name_f] [varchar](25) NULL,
	[name_m] [varchar](25) NULL,
	[name_l] [varchar](25) NULL,
	[department] [char](5) NULL,
	[status] [int] NULL,
 CONSTRAINT [PK_tbl_account_new] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_account]  WITH CHECK ADD  CONSTRAINT [tbl_master_tbl_account] FOREIGN KEY([id_key])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_account] CHECK CONSTRAINT [tbl_master_tbl_account]
GO
