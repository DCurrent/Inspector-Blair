UPDATE tbl_audit_question_reference
SET fk_id = _source.id_key
FROM tbl_audit_question _source
WHERE fk_id_old = _source.id_old