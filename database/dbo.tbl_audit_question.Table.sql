
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[tbl_audit_question](
	[id_key] [int] NOT NULL,
	[id_old] [uniqueidentifier] NOT NULL,
	[record_deleted] [bit] NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[finding] [varchar](max) NULL,
	[corrective_action] [varchar](max) NULL,
	[status] [smallint] NULL,
 CONSTRAINT [PK__tbl_audi__3213E83F0BB1B5A5] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

ALTER TABLE [dbo].[tbl_audit_question] ADD  CONSTRAINT [DF__tbl_audit_qu__id__0D99FE17]  DEFAULT (newid()) FOR [id_old]
GO

ALTER TABLE [dbo].[tbl_audit_question] ADD  CONSTRAINT [DF__tbl_audit__recor__0E8E2250]  DEFAULT ((0)) FOR [record_deleted]
GO


