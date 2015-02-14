<?php
$magicWords = array();

/** English
 */
$magicWords['en'] = array(
   'ArticleProtection' => array( 0, 'ArticleProtection' ),
);

/** 
 * Please customize this according to your needs by editing the message in
 * the wiki directly. For example try editing the page MediaWiki:approve-mail-subject-submitter
 */

$messages['en'] = array(
  // the Special:Log log name that appears in the drop-down on the Special:Log page
  'log-name-ArticleProtection' => 'ArticleProtection log',
 
  // the Special:Log description that appears on the Special:Log page when you filter logs 
  // on this specific log name
  'log-description-ArticleProtection' => 'These events track when ArticleProtection events happen in the system.',
 
  // the template of the log entry message
  'logentry-ArticleProtection-added-edit-permissions' => '$1 added edit permissions for page $3 to following users $4',
  'logentry-ArticleProtection-removed-edit-permissions' => '$1 removed edit permissions for page $3 to following users $4',
  'logentry-ArticleProtection-owner-created-permissions' => '$1 received ownership for creating the page $3',
 );
