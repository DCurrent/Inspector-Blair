USE [inspection]
GO
/****** Object:  Table [dbo].[tbl_area]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tbl_area](
	[id_key] [int] NOT NULL,
	[label] [varchar](50) NULL,
	[details] [varchar](max) NULL,
	[code] [varchar](6) NULL,
	[radiation_usage] [bit] NULL,
	[x_ray_usage] [bit] NULL,
	[hazardous_waste_generated] [bit] NULL,
	[chemical_lab_class] [int] NULL,
	[chemical_operations_class] [int] NULL,
	[ibc_protocal] [varchar](50) NULL,
	[biosafety_level] [int] NULL,
	[nfpa45_lab_unit] [int] NULL,
	[laser_usage] [bit] NULL,
 CONSTRAINT [PK_tbl_area_master] PRIMARY KEY CLUSTERED 
(
	[id_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[tbl_area]  WITH CHECK ADD  CONSTRAINT [FK_tbl_area_master_tbl_master] FOREIGN KEY([id_key])
REFERENCES [dbo].[tbl_master] ([id_key])
ON UPDATE CASCADE
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[tbl_area] CHECK CONSTRAINT [FK_tbl_area_master_tbl_master]
GO
