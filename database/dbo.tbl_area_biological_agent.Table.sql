USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_area_biological_agent]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_area_biological_agent](
	[id_key] [int] IDENTITY(1,1) NOT NULL,
	[fk_id] [int] NULL,
	[item] [int] NULL,
 CONSTRAINT [PK_tbl_area_biological_agent] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_area_biological_agent]  WITH CHECK ADD  CONSTRAINT [FK_tbl_area_biological_agent_tbl_area] FOREIGN KEY([fk_id])
REFERENCES [dbo].[tbl_area] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_area_biological_agent] CHECK CONSTRAINT [FK_tbl_area_biological_agent_tbl_area]
GO
