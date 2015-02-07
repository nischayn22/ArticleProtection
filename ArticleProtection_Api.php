<?php

class ApiArticleProtection extends ApiBase {

    public function execute() {
        global $wgScript, $wgUser;

        $article_id = $this->getMain()->getVal('article_id');
        $edit_permissions = $this->getMain()->getVal('edit_permissions');
        $view_permissions = $this->getMain()->getVal('view_permissions');

		$dbw = wfGetDB( DB_MASTER );

		$article_editors = explode(",", $edit_permissions);
		$article_viewers = explode(",", $view_permissions);

		$dbw->begin();
		foreach($article_viewers as $viewer) {
			$dbw->replace(
				'article_protection',
				array(
					array(
						'article_id',
						'user_name',
					)
				),
				array(
					'article_id' => $article_id,
					'user_name' => trim($viewer),
					'owner' => 0,
					'edit_permission' => 0,
					'view_permission' => 1
				)
			);
		}
		$dbw->commit();
		$dbw->begin();
		foreach($article_editors as $editor) {
			$dbw->replace(
				'article_protection',
				array(
					array(
						'article_id',
						'user_name',
					)
				),
				array(
					'article_id' => $article_id,
					'user_name' => trim($editor),
					'owner' => 0,
					'edit_permission' => 1,
					'view_permission' => 1,
				)
			);
		}
		$dbw->commit();
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
            'view_permissions' => array (
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