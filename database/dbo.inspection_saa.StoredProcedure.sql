USE [inspection]
GO
/****** Object:  StoredProcedure [dbo].[inspection_saa]    Script Date: 2017-03-24 02:27:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-07-27
-- Description:	Get single record detail.
-- =============================================

CREATE PROCEDURE [dbo].[inspection_saa]
	
	-- filter
	@param_filter_id		int	= NULL,
	@param_filter_id_key	int = NULL
		
AS	
	SET NOCOUNT ON;

	-- Create and populate the main data cache. This is 
	-- where we will do most (if not all) of our JOINs, 
	-- sorting and filtering to create a complete record set of
	-- primary data for consumption. We use a temporary
	-- table for performance and convenience. This temp table
	-- is also available in any other procedures we might call
	-- while this one is running. If we remember to use a 
	-- consistent naming convention, that will in turn allow us 
	-- to encapsulate a lot of repetitive work into reusable sub 
	-- procedures and keep their parameters to a bare minimum.		
		SELECT			
				_master.id,
				_master.id_key, 
				_master.active,
				_main.label, 
				_main.details,
				_main.room_code,
				_room.RoomID,
				_building.BuildingCode,
				_building.BuildingName,
				_master.create_time,
				_main.type
		INTO #cache_primary					
		FROM dbo.tbl_inspection_primary AS _main
			JOIN tbl_master _master ON _main.id_key = _master.id_key 
			LEFT JOIN
				 UKSpace.dbo.Rooms AS _room ON _main.room_code = _room.LocationBarCodeID
            LEFT JOIN
				UKSpace.dbo.MasterBuildings AS _building ON _room.Building = _building.BuildingCode
		WHERE
			-- Normal filter. This produces an active 
			-- revision list of all records.
			(@param_filter_id_key IS NULL AND _master.active = 1)
			OR
			-- Key filter. Get a specfic revision 
			-- of record by its ID key.
			(_master.id_key = @param_filter_id_key)			
		ORDER BY _main.label
			
	-- Navigation. This executes the navigation
	-- procedure, which produces a recordset
	-- including next ID, last ID, etc. for
	-- use by the control code to create record
	-- navigation buttons. See the stored 
	-- procedure for details.
		EXEC master_navigation @param_filter_id

	-- Select and output recordsets of data.

		-- Main (primary) data. We've already done all of
		-- the data processing. Just output the recordset
		-- filtered with ID.
		SELECT
			* 
		FROM 
			#cache_primary AS _data
		WHERE _data.id = @param_filter_id	
	
		-- Subsets. Once all the work is done for our primary table 
		-- and associated ancillary functionality, we can now output
		-- any sub data record sets. 
		
			-- First thing we need is the key id to
			-- relate to the the subsets foreign keys.
			-- We'll grab that from the finished main 
			-- record set and store it as a variable.
			DECLARE @id_key int = NULL
			SET @id_key = (SELECT TOP 1 id_key FROM #cache_primary WHERE id = @param_filter_id)

			-- Responsible parties				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.item
				FROM tbl_inspection_primary_party _main 					
				-- Filter to get only sub records related to main record set.
				WHERE _main.fk_id = @id_key

			-- Visit
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.visit_by,
					_main.visit_type,
					_main.time_recorded
				FROM tbl_inspection_primary_visit _main    
				WHERE _main.fk_id = @id_key

			-- Details				
				SELECT 
					_main.id_key,
					_main.fk_id,
					_main.label,
					_main.details,
					_main.correction,
					_main.complete
				FROM tbl_inspection_primary_detail _main
				WHERE _main.fk_id = @id_key
GO
