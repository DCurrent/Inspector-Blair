INSERT INTO tbl_account_role
(fk_id, item)
SELECT id,  FROM tbl_account AS _account
JOIN
	tbl_master AS _master ON _master.id_key = _account.id_key
WHERE _master.id NOT IN(933, 117, 175, 191, 256, 352, 418, 460, 650, 684, 699, 744) 