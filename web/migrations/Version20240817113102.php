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
        $this->addSql('
        DELIMITER //
        CREATE PROCEDURE count_tickets_service(IN service_id INT)
        BEGIN
            SELECT 
                (SELECT COUNT(*) FROM ticket WHERE service_id = service_id) AS total,
                (SELECT COUNT(*) FROM ticket WHERE service_id = service_id AND result_date IS NULL) AS in_progress,
                (SELECT COUNT(*) FROM ticket WHERE service_id = service_id AND result_date IS NOT NULL) AS closed;
        END //
        DELIMITER ;
    ');

        $this->addSql('
        DELIMITER //
        CREATE PROCEDURE count_tickets_user(IN user_id INT)
        BEGIN
            SELECT 
                (SELECT COUNT(*) FROM treatment WHERE user_id = user_id) AS n_open,
                (SELECT COUNT(*) FROM ticket WHERE creator_id = user_id) AS n_create,
                (SELECT COUNT(*) 
                 FROM treatment t
                 JOIN ticket ti ON t.ticket_id = ti.id
                 WHERE t.user_id = user_id AND t.end_date = ti.result_date) AS n_close;
        END //
        DELIMITER ;
    ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
