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
			$this->showForms();
			return;
		}

		$this->showUserPages($subPage);
	}

	public function showForms() {
		global $wgOut, $wgUser, $wgScriptPath;

		$wgOut->addHTML( '<h3>Enter usernames seperated by commas.</h3>' );

		$dbr = wfGetDB( DB_SLAVE );

		$articles = $dbr->select(
			'article_protection',
			array(
				'article_id',
			),
			array(
				'user_name' => $wgUser->getName(),
				'owner' => 1
			)
		);

		foreach ( $articles as $article ) {

			$article_user_permissions = $dbr->select(
				'article_protection',
				array(
					'article_id',
					'user_name',
					'edit_permission',
					'view_permission'
				),
				array(
					'article_id' => $article->article_id,
					'owner' => 0
				)
			);

			$article_editors = array();
			$article_viewers = array();

			$title_name = Title::newFromID( $article->article_id )->getFullText();
			foreach( $article_user_permissions as $article_user_perm ) {
				if ( $article_user_perm->edit_permission == 1 ) {
					$article_editors[] = $article_user_perm->user_name;
					continue;
				}
				if ( $article_user_perm->view_permission == 1 ) {
					$article_viewers[] = $article_user_perm->user_name;
				}
			}

			$edit_permissions_usernames = implode(",", $article_editors);
			$view_permissions_usernames = implode(",", $article_viewers);

			$htmlOut = Html::openElement( 'form',
				array(
					'name' => 'article_protection',
					'class' => 'article_protection_form',
					'id' => 'article_protection_form' . $article->article_id,
				)
			);
			$htmlOut .= Html::openElement( 'table',
				array(
					'class' => 'wikitable',
				)
			);
			$htmlOut .= Html::openElement( 'tbody');

			$htmlOut .= Html::openElement( 'tr');
			$title_name = Title::newFromID( $article->article_id )->getFullText();
			$htmlOut .= Html::rawElement( 'td',
				array(
					'colspan' => '2',
				),
				"Permissions for article $title_name"
			);
			$htmlOut .= Html::closeElement( 'tr');

			$htmlOut .= Html::openElement( 'tr');
			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_header',
				),
				'Usernames with edit permissions'
			);

			$htmlOut .= Html::openElement( 'td',
				array(
					'class' => 'article_protection_value',
				)
			);
			$htmlOut .= Html::element( 'textarea',
				array(
					'name' => 'edit_permissions',
					'id' => "article_protection_edit",
					'cols' => '5',
					'rows' => '5',
				),
				$edit_permissions_usernames
			);
			$htmlOut .= Html::closeElement( 'td');
			$htmlOut .= Html::closeElement( 'tr');

			$htmlOut .= Html::openElement( 'tr');
			$htmlOut .= Html::rawElement( 'td',
				array(
					'class' => 'article_protection_header',
				),
				'Usernames with view permissions'
			);

			$htmlOut .= Html::openElement( 'td',
				array(
					'class' => 'article_protection_value',
				)
			);
			$htmlOut .= Html::element( 'textarea',
				array(
					'name' => 'view_permissions',
					'id' => "article_protection_view",
					'cols' => '5',
					'rows' => '5',
				),
				$view_permissions_usernames
			);
			$htmlOut .= Html::closeElement( 'td');
			$htmlOut .= Html::closeElement( 'tr');

			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'hidden',
					'id' => "article_protection_id",
					'name' => "article_id",
					'value' => $article->article_id,
				)
			);

			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'hidden',
					'name' => "action",
					'value' => "article_protection",
				)
			);

			$htmlOut .= Html::openElement( 'tr');
			$htmlOut .= Html::openElement( 'td',
				array(
					'colspan' => '2',
				)
			);
			$htmlOut .= Html::element( 'input',
				array(
					'type' => 'submit',
					'id' => 'article_protection_save',
					'value' => "Save",
				)
			);
			$htmlOut .= Html::closeElement( 'td');
			$htmlOut .= Html::closeElement( 'tr');

			$htmlOut .= Xml::closeElement( 'tbody' );
			$htmlOut .= Xml::closeElement( 'table' );


			$htmlOut .= Xml::closeElement( 'form' );

			$wgOut->addHTML($htmlOut);
		}
		$wgOut->addModules( 'ext.articleprotection.edit' );
	}

	public function showUserPages($username) {
		global $wgOut;

		$user = User::newFromName( $username );
		if (!$user || ($user->getId() == 0)) {
			$wgOut->addHTML( '<p>This is not a valid username.</p>' );
			return;
		}

		$wgOut->addHTML("<p> To make permission changes to articles created by you click <a href=" . Title::newFromText('Special:ArticleProtection')->getFullURL() . ">here</a>.</p>");
		
		$dbr = wfGetDB( DB_SLAVE );

		$articles = $dbr->select(
			'article_protection',
			array(
				'article_id',
			),
			array(
				'user_name' => $user->getName(),
				'owner' => 1
			)
		);

		foreach ( $articles as $article ) {

			$article_user_permissions = $dbr->select(
				'article_protection',
				array(
					'article_id',
					'user_name',
					'edit_permission',
					'view_permission'
				),
				array(
					'article_id' => $article->article_id,
					'owner' => 0
				)
			);

			$article_editors = array();
			$article_viewers = array();

			$title_name = Title::newFromID( $article->article_id )->getFullText();
			foreach( $article_user_permissions as $article_user_perm ) {
				if ( $article_user_perm->edit_permission == 1 ) {
					$article_editors[] = $article_user_perm->user_name;
					continue;
				}
				if ( $article_user_perm->view_permission == 1 ) {
					$article_viewers[] = $article_user_perm->user_name;
				}
			}

			$edit_permissions_usernames = implode(",", $article_editors);
			if (empty($edit_permissions_usernames))
				$edit_permissions_usernames = "None";
			$view_permissions_usernames = implode(",", $article_viewers);
			if (empty($view_permissions_usernames))
				$view_permissions_usernames = "None";

		$output = <<<END

<table class="wikitable article_protection_table">
  <tr>
    <th colspan=2>Article $title_name created by $username</th>
  </tr>
  <tr>
    <td class="article_protection_header">Usernames with edit permissions</td>
    <td class="article_protection_value">$edit_permissions_usernames</td>
  </tr>
  <tr>
    <td class="article_protection_header">Usernames with view permissions</td>
    <td class="article_protection_value">$view_permissions_usernames</td>
  </tr>
</table>
END;

			$wgOut->addHTML($output);
		}

		$wgOut->addModules( 'ext.articleprotection.view' );
	}
}