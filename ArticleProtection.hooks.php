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
			'article_protection',
			array(
				'article_id' => $article->getID(),
				'user_name' => $user->getName(),
				'owner' => 1,
				'edit_permission' => 1,
			)
		);
		return true;
	}

	public static function onSkinTemplateNavigation( SkinTemplate &$sktemplate, array &$links ) {
		global $wgTitle;
		$request = $sktemplate->getRequest();
		$action = $request->getText( 'action' );
		$article_details = $sktemplate->makeArticleUrlDetails( Title::newFromText('Special:ArticleProtection/' . $wgTitle->getText() )->getFullText() );
		$links['views']['protection'] = array(
			'class' => false,
			'text' => "Protection",
			'href' => $article_details['href']
		);
		return true;
	}

	public static function onTitleQuickPermissions( $title, $user, $action, &$errors, $doExpensiveQueries, $short ) {

		// if ($action == 'edit') {

			// $dbr = wfGetDB( DB_SLAVE );

			// $article_info = $dbr->selectRow(
				// 'article_protection',
				// array(
					// 'owner',
					// 'edit_permission',
					// 'view_permission'
				// ),
				// array(
					// 'user_name' => $user->getName(),
					// 'article_id' => $title->getArticleID()
				// )
			// );

			// if (!$article_info) {
				// return true;
			// }

			// if ($article_info->edit_permission != "1" ) {
				// $errors = array( array( "not allowed" ) );
				// return false;
			// }
		// }
		return true;
	}

	public static function onUserGetRights( $user, &$aRights ) {
		global $wgTitle;
		$aRights = array_merge( array_diff($aRights, array("edit")) );
		$dbr = wfGetDB( DB_SLAVE );

		$article_info = $dbr->selectRow(
			'article_protection',
			array(
				'owner',
				'edit_permission'
			),
			array(
				'user_name' => $user->getName(),
				'article_id' => $wgTitle->getArticleID()
			)
		);

		if ( !$article_info ) {
			$aRights[] = "edit";
			return true;
		}

		if ($article_info->owner == "1" || $article_info->edit_permission == "1" ) {
			$aRights[] = "edit";
			return true;
		}

		return true;
	}
}
