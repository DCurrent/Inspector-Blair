UPDATE tbl_audit_question_inclusion
SET item = _source.id_key
FROM tbl_audit_question_inclusion_source _source
WHERE item_id = _source.id_old
