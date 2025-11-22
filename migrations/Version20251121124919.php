<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create accounts, transfers and ledger_entries tables';
    }

    public function up(Schema $schema): void
    {
        // Accounts
        $this->addSql(<<<'SQL'
CREATE TABLE accounts (
    id INT AUTO_INCREMENT NOT NULL,
    uuid VARCHAR(36) NOT NULL,
    owner_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
    currency VARCHAR(3) NOT NULL COLLATE utf8mb4_unicode_ci,
    balance_cents BIGINT NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    UNIQUE INDEX UNIQ_CAC89EACD17F50A6 (uuid),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );

        // Transfers
        $this->addSql(<<<'SQL'
CREATE TABLE transfers (
    uuid VARCHAR(36) NOT NULL,
    amount_cents BIGINT NOT NULL,
    currency VARCHAR(3) NOT NULL COLLATE utf8mb4_unicode_ci,
    idempotency_key VARCHAR(128) NOT NULL COLLATE utf8mb4_unicode_ci,
    metadata JSON DEFAULT NULL,
    status ENUM('CREATED','PENDING','COMPLETED','FAILED') NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    completed_at DATETIME(6) DEFAULT NULL,
    from_account_id INT NOT NULL,
    to_account_id INT NOT NULL,
    UNIQUE INDEX UNIQ_802A39187FD1C147 (idempotency_key),
    INDEX IDX_802A3918B0CF99BD (from_account_id),
    INDEX IDX_802A3918BC58BDC7 (to_account_id),
    PRIMARY KEY (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );

        // Ledger entries
        $this->addSql(<<<'SQL'
CREATE TABLE ledger_entries (
    id INT AUTO_INCREMENT NOT NULL,
    entry_type ENUM('DEBIT','CREDIT') NOT NULL,
    amount_cents BIGINT NOT NULL,
    balance_after_cents BIGINT NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    transfer_id VARCHAR(36) NOT NULL,
    account_id INT NOT NULL,
    INDEX IDX_E3FD73F4537048AF (transfer_id),
    INDEX IDX_E3FD73F49B6B5FBA (account_id),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );

        // Foreign keys
        $this->addSql('ALTER TABLE ledger_entries ADD CONSTRAINT FK_E3FD73F4537048AF FOREIGN KEY (transfer_id) REFERENCES transfers (uuid) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE ledger_entries ADD CONSTRAINT FK_E3FD73F49B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfers ADD CONSTRAINT FK_802A3918B0CF99BD FOREIGN KEY (from_account_id) REFERENCES accounts (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transfers ADD CONSTRAINT FK_802A3918BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES accounts (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // Drop FKs
        $this->addSql('ALTER TABLE ledger_entries DROP FOREIGN KEY FK_E3FD73F4537048AF');
        $this->addSql('ALTER TABLE ledger_entries DROP FOREIGN KEY FK_E3FD73F49B6B5FBA');
        $this->addSql('ALTER TABLE transfers DROP FOREIGN KEY FK_802A3918B0CF99BD');
        $this->addSql('ALTER TABLE transfers DROP FOREIGN KEY FK_802A3918BC58BDC7');

        $this->addSql('DROP TABLE ledger_entries');
        $this->addSql('DROP TABLE transfers');
        $this->addSql('DROP TABLE accounts');
    }
}
