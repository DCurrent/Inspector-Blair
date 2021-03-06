USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_role_access]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_role_access](
	[id] [uniqueidentifier] ROWGUIDCOL  NOT NULL,
	[fk_id] [uniqueidentifier] NULL,
	[access] [int] NULL,
	[log_create] [datetime2](7) NULL,
	[log_update] [datetime2](7) NULL,
	[log_update_by] [varchar](10) NULL,
	[log_update_ip] [varchar](50) NULL,
	[record_deleted] [bit] NULL,
 CONSTRAINT [PK_tbl_role_access] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_role_access] ADD  CONSTRAINT [DF_tbl_role_access_id]  DEFAULT (newid()) FOR [id]
GO
ALTER TABLE [dbo].[tbl_role_access] ADD  CONSTRAINT [DF_tbl_role_access_log_create]  DEFAULT (getdate()) FOR [log_create]
GO
ALTER TABLE [dbo].[tbl_role_access] ADD  CONSTRAINT [DF_tbl_role_access_log_update]  DEFAULT (getdate()) FOR [log_update]
GO
