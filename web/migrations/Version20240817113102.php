<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240817113102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM doctrine_migration_versions WHERE version LIKE '%Version20240817113102';");

        $this->addSql('
        CREATE TRIGGER IF NOT EXISTS `treatment_after_update` AFTER UPDATE ON `treatment` FOR EACH ROW 
        BEGIN
             UPDATE ticket
                SET 
                    ticket.result_date = (SELECT MAX(treatment.end_date)
                                              FROM treatment
                                              WHERE treatment.ticket_id = NEW.ticket_id
                                                AND treatment.end_date IS NOT NULL),
                     ticket.result = (SELECT treatment.observations
                                         FROM treatment
                                         WHERE treatment.end_date = (SELECT MAX(treatment.end_date)
                                                                     FROM treatment
                                                                     WHERE treatment.ticket_id = NEW.ticket_id
                                                                       AND treatment.end_date IS NOT NULL)
                                           AND treatment.ticket_id = NEW.ticket_id)
                WHERE ticket.id = NEW.ticket_id AND NEW.end_date IS NOT NULL;
        END        
        ');
        $this->addSql("
        CREATE PROCEDURE IF NOT EXISTS  count_tickets_service(IN serviceId INT)
        BEGIN
            SELECT
                COUNT(CASE WHEN t.id NOT IN (SELECT DISTINCT ticket_id FROM treatment)
                            OR latest_treatment.status = 'EN ATTENTE' THEN 1 END) AS in_waiting,
                COUNT(CASE WHEN latest_treatment.status = 'EN COURS' THEN 1 END) AS in_progress,
                COUNT(CASE WHEN latest_treatment.status = 'TERMINÉ' THEN 1 END) AS closed
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
        ");

        $this->addSql('
        CREATE PROCEDURE IF NOT EXISTS count_tickets_user(IN userId INT)
        BEGIN
            SELECT 
            (SELECT COUNT(*) FROM treatment WHERE caterer_id = userId) AS n_open,
            (SELECT COUNT(*) FROM ticket WHERE creator_id = userId) AS n_create,
            (SELECT COUNT(*) FROM treatment t JOIN ticket ti ON t.ticket_id = ti.id WHERE t.caterer_id = userId AND t.end_date = ti.result_date AND t.`status` = "TERMINÉ") AS n_close;
        END
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
