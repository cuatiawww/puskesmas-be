-- DDL Alterations for forgot password feature
-- Run this if the columns do not already exist in public.user table

ALTER TABLE public."user" ADD COLUMN IF NOT EXISTS password_reset_otp VARCHAR(255);
ALTER TABLE public."user" ADD COLUMN IF NOT EXISTS password_reset_otp_expires_at TIMESTAMP;
ALTER TABLE public."user" ADD COLUMN IF NOT EXISTS password_reset_requested_at TIMESTAMP;
