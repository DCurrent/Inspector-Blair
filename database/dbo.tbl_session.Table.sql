USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_session]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_session](
	[session_id] [varchar](40) NOT NULL,
	[session_data] [varchar](max) NULL,
	[last_update] [datetime2](7) NULL,
	[source] [varchar](max) NULL,
	[host] [varchar](15) NULL,
 CONSTRAINT [PK_tbl_php_sessions] PRIMARY KEY CLUSTERED 
(
	[session_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
