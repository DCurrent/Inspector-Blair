USE inspection

	
INSERT INTO dbo.tbl_master  (active, 
							create_by,
							create_host,
							update_by,
							update_host,
							temp_id)
						SELECT
							1,
							933,
							'10.163.4.86',
							933,
							'10.163.4.86',
							id_old
						FROM tbl_inspection_event_source

UPDATE
    tbl_inspection_event_source
SET
    id_key = _master.id_key
FROM tbl_master _master
WHERE
    id_old = _master.temp_id; 

UPDATE
	tbl_master 
SET id = id_key 
WHERE
	id = -1			
    
