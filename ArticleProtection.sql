-- MySQL version of the database schema for the ArticleProtection extension.
-- Licence: GNU GPL v3+
-- Author: Nischay Nahata < nischayn22@gmail.com >

-- table for records of each article.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/article_protection (
  article_id              INT(10) unsigned    NOT NULL, -- Foreign key: page.page_id
  user_name 			  varchar(255) binary NOT NULL default '',
  owner			          bool NOT NULL default 0,
  edit_permission         bool NOT NULL default 0,
  view_permission         bool NOT NULL default 0,
  PRIMARY KEY  (article_id, user_name)
) /*$wgDBTableOptions*/;