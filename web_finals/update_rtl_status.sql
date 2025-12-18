-- Update RTL status enum to include 'verified' and 'rejected'
ALTER TABLE `rtl` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'completed', 'verified', 'rejected', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending';
