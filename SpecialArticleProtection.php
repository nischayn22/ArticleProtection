<?php

/**
 * 
 * @since 0.1
 * 
 * @file SpecialArticleProtection.php
 * @ingroup ArticleProtection
 * 
 * @licence GNU GPL v3 or later
 * @author Nischay Nahata < nischayn22@gmail.com >
 */
class SpecialArticleProtection extends SpecialPage {

	/**
	 * Constructor.
	 * 
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'ArticleProtection' );
	}
	
	/**
	 * @see SpecialPage::getDescription
	 * 
	 * @since 0.1
	 */
	public function getDescription() {
		return "Article Protection";
	}
	
	/**
	 * Sets headers - this should be called from the execute() method of all derived classes!
	 * 
	 * @since 0.1
	 */
	public function setHeaders() {
		global $wgOut;
		$wgOut->setArticleRelated( false );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );
		$wgOut->setPageTitle( $this->getDescription() );
	}	
	
	/**
	 * Main method.
	 * 
	 * @since 0.1
	 * 
	 * @param string $arg
	 */
	public function execute( $subPage ) {
		global $wgOut, $wgUser;
		
		$this->setHeaders();
		$this->outputHeader();

		if( empty( $subPage ) && !$wgUser->isLoggedIn()) {
			$this->displayRestrictionError();
			return;
		}

		if ( empty( $subPage ) ) {
			$this->showFormLinks($wgUser->getName());
			return;
		}

		if (strpos( $subPage, 'UserPermissions:' ) !== false) {
			$this->showFormLinks(substr( $subPage, 16 ));
			return;
		}

		$wgOut->setPageTitle('Protection for ' . str_replace( '_', ' ', $subPage));
		$this->showArticlePermissions($subPage);
	}

	public function showFormLinks( $username ) {
		global $wgOut, $wgUser;
		$dbr = wfGetDB( DB_SLAVE );

		$articles = $dbr->select(
			'article_protection',
			array(
				'article_id',
			),
			array(
				'user_name' => $username,
				'owner' => 1
			)
		);

		if ( $username == $wgUser->getName() ) {
			$wgOut->addHTML( "<h3>My pages</h3>" );
		} else {
			$wgOut->addHTML( "<h3>Pages owned by $username</h3>" );
		}

		$htmlOut = Html::openElement( 'div' );
		$htmlOut .= Html::openElement( 'table',
			array(
				'class' => 'wikitable',
				'style' => 'width:100%;',
			)
		);
		$htmlOut .= Html::openElement( 'tbody' );

		$htmlOut .= Html::openElement( 'tr' );
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Article"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Owner(s)"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Editor(s)"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Permissions"
		);
		$htmlOut .= Html::closeElement( 'tr' );
		$wgOut->addHTML($htmlOut);

		foreach ( $articles as $article ) {

			$article_user_permissions = $dbr->select(
				'article_protection',
				array(
					'article_id',
					'user_name',
					'original_owner',
					'owner',
					'edit_permission'
				),
				array(
					'article_id' => $article->article_id
				)
			);

			$this_user_can_edit = false;
			$this_user_is_owner = false;

			$original_owner_username = '';
			$article_original_owners = array();
			$article_owners = array();
			$article_editors = array();

			$title = Title::newFromID( $article->article_id );
			$title_name = $title->getFullText();
			foreach( $article_user_permissions as $article_user_perm ) {
				if ( $article_user_perm->original_owner == 1 ) {
					$article_original_owners[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					$original_owner_username = $article_user_perm->user_name;
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_can_edit = true;
						$this_user_is_owner = true;
					}
				}
				if ( $article_user_perm->owner == 1 ) {
					$article_owners[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_is_owner = true;
						$this_user_can_edit = true;
					}
				}
				if ( $article_user_perm->edit_permission == 1 ) {
					$article_editors[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_can_edit = true;
					}
				}
			}

			$original_owner_permissions_usernames = implode(",", $article_original_owners);
			$owner_permissions_usernames = implode(",", $article_owners);
			$edit_permissions_usernames = implode(",", $article_editors);
			if (empty($edit_permissions_usernames)) {
				$edit_permissions_usernames = 'None';
			}

			$htmlOut = Html::openElement( 'tr' );
			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row',
				),
				$this_user_can_edit ? Linker::link($title) . " (" . Linker::link($title, "edit", array(), array("action" => "edit")) . ")" : Linker::link($title)
			);

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row article_protection_row_long',
				),
				$owner_permissions_usernames
			);

			$edit_perms_link = Linker::link( Title::newFromText( "Special:ArticleProtection/" . $title_name ), "View" );
			if( $this_user_is_owner ) {
				$edit_perms_link .= " / " . Linker::link( Title::newFromText( "Special:ArticleProtection/Settings:" . $title_name ), "Modify" );
			}

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row article_protection_row_long',
				),
				$edit_permissions_usernames
			);

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row',
				),
				$edit_perms_link . " / " . Linker::link( Title::newFromText( "Special:Log" ), "Log", array(), array( "type" => "ArticleProtection", "page" => $title_name ) )
			);
			$htmlOut .= Html::closeElement( 'tr' );

			$wgOut->addHTML($htmlOut);
		}
		$htmlOut = Xml::closeElement( 'tbody' );
		$htmlOut .= Xml::closeElement( 'table' );
		$htmlOut .= Xml::closeElement( 'div' );

		$wgOut->addHTML($htmlOut);


		
		// Table for articles user has only edit permissions to
		$articles = $dbr->select(
			'article_protection',
			array(
				'article_id',
			),
			array(
				'user_name' => $username,
				'owner' => 0,
				'edit_permission' => 1
			)
		);

		if ( $username == $wgUser->getName() ) {
			$wgOut->addHTML( '<h3>Pages that I can edit.</h3>' );
		} else {
			$wgOut->addHTML( "<h3>Pages that $username can edit.</h3>" );
		}

		$htmlOut = Html::openElement( 'div' );
		$htmlOut .= Html::openElement( 'table',
			array(
				'class' => 'wikitable',
				'style' => 'width:100%;',
			)
		);
		$htmlOut .= Html::openElement( 'tbody' );

		$htmlOut .= Html::openElement( 'tr' );
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Article"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Owner(s)"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Editor(s)"
		);
		$htmlOut .= Html::rawElement( 'td',
			array(
				'class' => 'article_protection_header',
			),
			"Permissions"
		);
		$htmlOut .= Html::closeElement( 'tr' );
		$wgOut->addHTML($htmlOut);

		foreach ( $articles as $article ) {

			$article_user_permissions = $dbr->select(
				'article_protection',
				array(
					'article_id',
					'user_name',
					'original_owner',
					'owner',
					'edit_permission'
				),
				array(
					'article_id' => $article->article_id
				)
			);

			$this_user_can_edit = false;
			$this_user_is_owner = false;

			$original_owner_username = '';
			$article_original_owners = array();
			$article_owners = array();
			$article_editors = array();

			$title = Title::newFromID( $article->article_id );
			$title_name = $title->getFullText();
			foreach( $article_user_permissions as $article_user_perm ) {
				if ( $article_user_perm->original_owner == 1 ) {
					$article_original_owners[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					$original_owner_username = $article_user_perm->user_name;
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_can_edit = true;
						$this_user_is_owner = true;
					}
				}
				if ( $article_user_perm->owner == 1 ) {
					$article_owners[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_can_edit = true;
						$this_user_is_owner = true;
					}
				}

				if ( $article_user_perm->edit_permission == 1 ) {
					$article_editors[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
					if ($article_user_perm->user_name == $wgUser->getName()) {
						$this_user_can_edit = true;
					}
				}
			}

			$original_owner_permissions_usernames = implode(",", $article_original_owners);
			$owner_permissions_usernames = implode(",", $article_owners);
			$edit_permissions_usernames = implode(",", $article_editors);
			if (empty($edit_permissions_usernames)) {
				$edit_permissions_usernames = 'None';
			}

			$htmlOut = Html::openElement( 'tr' );
			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row',
				),
				$this_user_can_edit ? Linker::link($title) . " (" . Linker::link($title, "edit", array(), array("action" => "edit")) . ")" : Linker::link($title)
			);

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row article_protection_row_long',
				),
				$owner_permissions_usernames
			);

			$edit_perms_link = Linker::link( Title::newFromText( "Special:ArticleProtection/" . $title_name ), "View" );
			if( $this_user_is_owner ) {
				$edit_perms_link .= " / " . Linker::link( Title::newFromText( "Special:ArticleProtection/Settings:" . $title_name ), "Modify" );
			}

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row',
				),
				$edit_permissions_usernames
			);

			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_row',
				),
				$edit_perms_link . ' / ' . Linker::link( Title::newFromText( "Special:Log" ), "Log", array(), array( "type" => "ArticleProtection", "page" => $title_name ) )
			);
			$htmlOut .= Html::closeElement( 'tr' );

			$wgOut->addHTML($htmlOut);
		}
		$htmlOut = Xml::closeElement( 'tbody' );
		$htmlOut .= Xml::closeElement( 'table' );
		$htmlOut .= Xml::closeElement( 'div' );

		$wgOut->addHTML($htmlOut);

		$wgOut->addModules( 'ext.articleprotection.edit' );
	}

	public function showArticlePermissions($subPage) {
		global $wgUser, $wgOut;
		$showEdit = false;
		$username = $wgUser->getName();

		$pageName = $subPage;
		if (strpos( $subPage, 'Settings:' ) !== false) {
			$pageName = substr($subPage, 9);
			$showEdit = true;
		}

		$title = Title::newFromText( $pageName );
		$article_id = $title->getArticleID();

		$dbr = wfGetDB( DB_SLAVE );

		$article_user_permissions = $dbr->select(
			'article_protection',
			array(
				'article_id',
				'user_name',
				'owner',
				'edit_permission'
			),
			array(
				'article_id' => $article_id
			)
		);

		$pageName = str_replace( '_', ' ', $pageName);
		$isMyPage = false;
		$article_owners = array();
		$article_editors = array();

		foreach( $article_user_permissions as $article_user_perm ) {
			if ( $article_user_perm->owner == 1 ) {
				$article_owners[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name ) . ' (' . Linker::link( Title::newFromText( "Special:ArticleProtection/UserPermissions:" . $article_user_perm->user_name ), "Pages" ) . ')';
				if ( $article_user_perm->user_name == $username )
					$isMyPage = true;
			}
			if ( $article_user_perm->edit_permission == 1 ) {
				$article_editers[] = Linker::link( Title::makeTitle( NS_USER, $article_user_perm->user_name), $article_user_perm->user_name );
				continue;
			}
		}
		$owner_permissions_usernames = implode(",", $article_owners);
		$edit_permissions_usernames = implode(",", $article_editors);
		$log_link = Linker::link( Title::newFromText( "Special:Log" ), "Log", array(), array( "type" => "ArticleProtection", "page" => $pageName ) );
		if ($showEdit && !$isMyPage) {
			$this->displayRestrictionError();
			return;
		}

		if (!$showEdit) {
			if (empty($owner_permissions_usernames))
				$owner_permissions_usernames = "None";
			if (empty($edit_permissions_usernames))
				$edit_permissions_usernames = "None";
			if ($isMyPage){
				$log_link = Linker::link( Title::newFromText( "Special:ArticleProtection/Settings:" . $pageName ), "Modify" ) . ' / ' . $log_link;
			}

			$pageLink = Linker::link($title);
			$output = <<<END

<table class="wikitable article_protection_table">
<tr>
<th colspan=2>Article Protection information about $pageLink</th>
</tr>
<tr>
<td class="article_protection_header">Owner(s)</td>
<td class="article_protection_value">$owner_permissions_usernames</td>
</tr>
<tr>
<td class="article_protection_header">Editor(s)</td>
<td class="article_protection_value">$edit_permissions_usernames</td>
</tr>
<tr>
<td class="article_protection_header">Permissions</td>
<td class="article_protection_value">$log_link</td>
</tr>
</table>
END;

			$wgOut->addHTML($output);
			$wgOut->addModules( 'ext.articleprotection.view' );
		} else {
			$wgOut->addHTML("<p> Enter usernames (separated by commas) to grant edit permissions for article <b>". $pageName ."</b> and click on save.</p>");
			$wgOut->addHTML('<div class="result_message"> </div>');
			$htmlOut = Html::openElement( 'form',
				array(
					'name' => 'article_protection',
					'class' => 'article_protection_form',
					'id' => 'article_protection_form' . $article_id,
				)
			);

			$htmlOut .= Html::openElement( 'table',
				array(
					'class' => 'wikitable',
				)
			);
			$htmlOut .= Html::openElement( 'tbody' );
			$htmlOut .= Html::openElement( 'tr' );
			$htmlOut .= Html::openElement( 'td'
			);
			$htmlOut .= Html::element( 'textarea',
				array(
					'name' => 'edit_permissions',
					'id' => "article_protection_edit",
					'cols' => '50',
					'rows' => '5',
				),
				$edit_permissions_usernames
			);
			$htmlOut .= Html::closeElement( 'td' );
			$htmlOut .= Html::closeElement( 'tr' );
			$htmlOut .= Xml::closeElement( 'tbody' );
			$htmlOut .= Xml::closeElement( 'table' );

			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'hidden',
					'id' => "article_protection_id",
					'name' => "article_id",
					'value' => $article_id,
				)
			);
			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'hidden',
					'name' => "action",
					'value' => "article_protection",
				)
			);
			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'submit',
					'id' => 'article_protection_save',
					'value' => "Save",
				)
			);


			$htmlOut .= Xml::closeElement( 'form' );
			$wgOut->addHTML($htmlOut);
			$wgOut->addModules( 'ext.articleprotection.edit' );
		}
	}
}