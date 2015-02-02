-- MySQL version of the database schema for the ArticleProtection extension.
-- Licence: GNU GPL v3+
-- Author: Nischay Nahata < nischayn22@gmail.com >

-- table for records of each article.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/article_protection (
  article_id              INT(10) unsigned    NOT NULL, -- Foreign key: page.page_id
  article_creator_id      INT(10) unsigned    NOT NULL, -- Foreign key: user.user_id
  article_editors         BLOB                NOT NULL, -- Array of user_ids
  article_viewers         BLOB                NOT NULL, -- Array of user_ids
  PRIMARY KEY  (article_id)
) /*$wgDBTableOptions*/;