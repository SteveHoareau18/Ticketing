CREATE DEFINER=`ticketing`@`%` PROCEDURE `count_tickets_service`(
    IN `serviceId` INT
)
    LANGUAGE SQL
    NOT DETERMINISTIC
    CONTAINS SQL
    SQL SECURITY DEFINER
    COMMENT ''
BEGIN
    SELECT
        COUNT(CASE WHEN t.id NOT IN (SELECT DISTINCT ticket_id FROM treatment)
            OR latest_treatment.status = 'EN ATTENTE' THEN 1 END) AS in_waiting,
        COUNT(CASE WHEN latest_treatment.status = 'EN COURS' THEN 1 END) AS in_progress,
        COUNT(CASE WHEN latest_treatment.status = 'Fermé' THEN 1 END) AS closed
    FROM ticket t
             LEFT JOIN (
        SELECT ticket_id, status
        FROM treatment t1
        WHERE t1.end_date = (
            SELECT MAX(t2.end_date)
            FROM treatment t2
            WHERE t2.ticket_id = t1.ticket_id
        )
    ) latest_treatment ON t.id = latest_treatment.ticket_id
    WHERE t.service_id = serviceId;
END