USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_inspection_saa_area_list]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_inspection_saa_area_list](
	[id] [uniqueidentifier] ROWGUIDCOL  NOT NULL,
	[id_old] [int] IDENTITY(1,1) NOT NULL,
	[record_deleted] [bit] NULL,
	[log_create] [datetime2](7) NULL,
	[log_create_by] [varchar](10) NULL,
	[log_create_ip] [varchar](50) NULL,
	[log_update] [datetime2](7) NULL,
	[log_update_by] [varchar](10) NULL,
	[log_update_ip] [varchar](50) NULL,
	[log_version] [timestamp] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
 CONSTRAINT [PK__tbl_saa___3213E83F4E53A1AA] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_inspection_saa_area_list] ADD  CONSTRAINT [DF_tbl_inspection_saa_area_list_id]  DEFAULT (newid()) FOR [id]
GO
ALTER TABLE [dbo].[tbl_inspection_saa_area_list] ADD  CONSTRAINT [DF__tbl_saa_a__recor__503BEA1C]  DEFAULT ((0)) FOR [record_deleted]
GO
ALTER TABLE [dbo].[tbl_inspection_saa_area_list] ADD  CONSTRAINT [DF__tbl_saa_a__log_c__51300E55]  DEFAULT (sysdatetime()) FOR [log_create]
GO
