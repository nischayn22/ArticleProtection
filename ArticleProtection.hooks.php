<?php

/**
 * Static class for hooks handled by the Page Creation Notification extension.
 *
 * @since 0.1
 *
 * @file ArticleProtection.hooks.php
 * @ingroup ArticleProtection
 *
 * @licence GNU GPL v3 or later
 * @author Nischay Nahata < nischayn22@gmail.com >
 */
final class ArticleProtectionHooks {

	/**
	 * Schema update to set up the needed database tables.
	 *
	 * @since 0.1
	 *
	 * @param DatabaseUpdater $updater
	 *
	 * @return true
	 */
	public static function onSchemaUpdate( /* DatabaseUpdater */ $updater = null ) {
		global $wgDBtype;

		if ( $wgDBtype == 'mysql' ) {
            $updater->addExtensionUpdate( array(
                'addTable',
                'article_protection',
                dirname( __FILE__ ) . '/ArticleProtection.sql',
                true
            ) );
		}

		return true;
	}

	/**
	 * Called just after a new Article is created.
	 *
	 * @since 0.1
	 */
	public static function onArticleInsertComplete( &$article, User &$user, $text, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, Revision 
	$revision ) {

		global $articleProtectionNS;
		if (!in_array( $article->getTitle()->getNamespace(), $articleProtectionNS ))
			return true;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'aritcle_protection',
			array(
				'article_id' => $article->getPage()->getID(),
				'article_creator_id' => $user->getId(),
				'article_editors' => '',
				'article_viewers' => ''
			)
		);
		return true;
	}

}
