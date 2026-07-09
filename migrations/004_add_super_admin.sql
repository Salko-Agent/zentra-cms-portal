-- Migration 004: Add is_super_admin column to users table
-- Fixes security issue: previously any user with role='admin' could see all projects
-- Now only users with is_super_admin=1 can switch projects and see cross-project data

ALTER TABLE `users`
  ADD COLUMN `is_super_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`;

-- Set Salih (BMS) as the only super admin
-- Adjust the WHERE clause if needed to match the correct user
UPDATE `users` SET `is_super_admin` = 1 WHERE `email` = 'salih@bmsdigitalsolutions.com';
