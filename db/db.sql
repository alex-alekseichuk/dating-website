-- script to create the database
-- should be runned by root
-- something like: mysql -u root mysql <db.sql
create database fyd;
grant all privileges on fyd.* to fyd@localhost identified by 'fyd21';
grant all privileges on fyd.* to fyd@'%' identified by 'fyd21';
