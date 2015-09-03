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
				'original_owner' => 1,
				'edit_permission' => 0,
			)
		);

		$logEntry = new ManualLogEntry( 'ArticleProtection', 'owner-created-permissions' );
		$logEntry->setPerformer( $user ); // User object, the user who performed this action
		$logEntry->setTarget( Title::newFromID( $article->getID() ) ); // The page that this log entry affects, a Title object
		$logid = $logEntry->insert();

		return true;
	}

	public static function onSkinTemplateNavigation( SkinTemplate &$sktemplate, array &$links ) {
		global $wgTitle, $articleProtectionNS, $wgOut;

		if (!in_array( $wgTitle->getNamespace(), $articleProtectionNS ))
			return true;

		$article_details = $sktemplate->makeArticleUrlDetails( Title::newFromText('Special:ArticleProtection/' . $wgTitle->getFullText() )->getFullText() );
		$links['views']['protection'] = array(
			'class' => false,
			'text' => "View editors",
			'href' => $article_details['href']
		);
		return true;
	}

	public static function BeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModules( 'ext.articleprotection.pageview' );
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
		global $wgTitle, $articleProtectionNS;

		if (!in_array( $wgTitle->getNamespace(), $articleProtectionNS )) {
			return true;
		}

		$aRights = array_merge( array_diff($aRights, array("edit", "minoredit", "reupload", "upload")) );

		if (!$user->isLoggedIn()) {
			return true;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$article_infos = $dbr->select(
			'article_protection',
			array(
				'owner',
				'user_name',
				'edit_permission'
			),
			array(
				'article_id' => $wgTitle->getArticleID()
			)
		);

		if ( !$article_infos->current() ) {
			$aRights[] = "edit";
			$aRights[] = "minoredit";
			$aRights[] = "upload";
			$aRights[] = "reupload";
			return true;
		}

		foreach($article_infos as $article_info) {
			if ($article_info->user_name != $user->getName()) {
				continue;
			}

			if ($article_info->owner == "1" || $article_info->edit_permission == "1" ) {
				$aRights[] = "edit";
				$aRights[] = "minoredit";
				$aRights[] = "upload";
				$aRights[] = "reupload";
				return true;
			}
		}

		return true;
	}
	
	public static function onAPIEditBeforeSave( $editPage, $text, &$resultArr ) {
		global $wgTitle, $wgUser, $articleProtectionNS;

		if ( !in_array( $wgTitle->getNamespace(), $articleProtectionNS ) ) {
			return true;
		}

		if ( !$wgUser->isLoggedIn() ) {
			$resultArr = array(
				'reason' => 'Anonymous edits not permitted for page.'
			);
			return false;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$article_infos = $dbr->select(
			'article_protection',
			array(
				'owner',
				'user_name',
				'edit_permission'
			),
			array(
				'article_id' => $wgTitle->getArticleID()
			)
		);

		if ( !$article_infos->current() ) {
			return true;
		}

		foreach ( $article_infos as $article_info ) {
			if ( $article_info->user_name != $wgUser->getName() ) {
				continue;
			}

			if ( $article_info->owner == "1" || $article_info->edit_permission == "1" ) {
				return true;
			}
		}

		$resultArr = array(
			'reason' => 'User does not have access to edit page.'
		);
		return false;
	}
}
