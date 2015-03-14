<?php

class ApiArticleProtection extends ApiBase {

    public function execute() {
        global $wgScript, $wgUser, $apMaxEditors;

        $article_id = $this->getMain()->getVal('article_id');
        $edit_permissions = $this->getMain()->getVal('edit_permissions');
		$new_article_editors = explode(",", $edit_permissions);

		if (count($new_article_editors) > $apMaxEditors) {
			$this->getResult()->addValue( 'result', "editor_limit_exceeded", $apMaxEditors);
			return;
		}

		// Clean usernames
		foreach($new_article_editors as &$editor) {
			$user = User::newFromName($editor);
			if ($user->getId() == 0) {
				$this->getResult()->addValue( 'result', "username_error", $editor);
				return;
			}
			$editor = $user->getName();
		}

		$dbw = wfGetDB( DB_MASTER );

		$article_user_permissions = $dbw->select(
			'article_protection',
			array(
				'article_id',
				'user_name',
				'original_owner',
				'owner',
				'edit_permission'
			),
			array(
				'article_id' => $article_id
			)
		);
		$old_article_editors = array();
		$this_user_is_owner = false;
		foreach( $article_user_permissions as $article_user_perm ) {
			if ( $article_user_perm->edit_permission == 1 ) {
				$old_article_editors[] = $article_user_perm->user_name;
			}
			if ( $article_user_perm->original_owner == 1 ) {
				if ($article_user_perm->user_name == $wgUser->getName()) {
					$this_user_is_owner = true;
				}
			}
			if ( $article_user_perm->owner == 1 ) {
				if ($article_user_perm->user_name == $wgUser->getName()) {
					$this_user_is_owner = true;
				}
			}
		}

		// Return silently if this was a hack attempt
		if (!$this_user_is_owner) {
			return;
		}

		$editors_removed = array_diff( $old_article_editors, $new_article_editors );
		$editors_added = array_diff( $new_article_editors, $old_article_editors );

		foreach($editors_removed as $editor) {
			$dbw->delete(
				'article_protection',
				array(
					'article_id' => $article_id,
					'user_name' => $editor,
				)
			);
		}
		foreach($editors_added as $editor) {
			$dbw->insert(
				'article_protection',
				array(
					'article_id' => $article_id,
					'user_name' => $editor,
					'owner' => 0,
					'edit_permission' => 1,
				)
			);
		}

		if (!empty($editors_added)) {
			$logEntry = new ManualLogEntry( 'ArticleProtection', 'added-edit-permissions' );
			$logEntry->setPerformer( $wgUser ); // User object, the user who performed this action
			$logEntry->setTarget( Title::newFromID( $article_id ) ); // The page that this log entry affects, a Title object
			$logEntry->setParameters( array(
			  '4::newusers' => implode(",", $editors_added)
			) );
			$logid = $logEntry->insert();

			$this->getResult()->addValue( "result", "added", implode(",", $editors_added));
		}

		if (!empty($editors_removed)) {
			$logEntry = new ManualLogEntry( 'ArticleProtection', 'removed-edit-permissions' );
			$logEntry->setPerformer( $wgUser ); // User object, the user who performed this action
			$logEntry->setTarget( Title::newFromID( $article_id ) ); // The page that this log entry affects, a Title object
			$logEntry->setParameters( array(
			  '4::oldusers' => implode(",", $editors_removed)
			) );
			$logid = $logEntry->insert();
			$this->getResult()->addValue( 'result', "removed", implode(",", $editors_removed));
		}
}

    public function getDescription() {
         return 'Api to change article protection.';
    }
 
    public function getAllowedParams() {
        return array_merge( (array)parent::getAllowedParams(), array(
            'article_id' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
            'edit_permissions' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
        ));
    }
 
    public function getParamDescription() {
        return array_merge( parent::getParamDescription(), array(
        ) );
    }
 
}