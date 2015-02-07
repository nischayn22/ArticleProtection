<?php
if ( ! defined( 'MEDIAWIKI' ) )
    die();

/**
 * Extension to manage article permissions
 *
 * @file
 * @author Nischay Nahata <nischayn22@gmail.com> for Wikiworks
 * @ingroup Extensions
 * @licence GNU GPL v3 or later
 */

define( 'ArticleProtection_VERSION', '0.1 beta' );

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'ArticleProtection',
	'version' => ArticleProtection_VERSION,
	'author' => array(
		'[http://www.mediawiki.org/wiki/User:Nischayn22 Nischay Nahata] for [http://www.wikiworks.com/ WikiWorks]',
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:ArticleProtection',
	'descriptionmsg' => 'article-protection-desc'
);

// Autoloading classes
$wgAutoloadClasses['ArticleProtectionHooks'] = dirname( __FILE__ ) . '/ArticleProtection.hooks.php';
$wgAutoloadClasses['ApiArticleProtection'] = __DIR__ . '/ArticleProtection_Api.php';

// Api classes
$wgAPIModules['article_protection'] = 'ApiArticleProtection';

// Hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'ArticleProtectionHooks::onSchemaUpdate';
$wgHooks['ArticleInsertComplete'][] = 'ArticleProtectionHooks::onArticleInsertComplete';
$wgHooks['TitleQuickPermissions'][] = 'ArticleProtectionHooks::onTitleQuickPermissions';
$wgHooks['SkinTemplateNavigation'][] = 'ArticleProtectionHooks::onSkinTemplateNavigation';
$wgHooks['UserGetRights'][] = 'ArticleProtectionHooks::onUserGetRights';

// Special pages
$wgAutoloadClasses['SpecialArticleProtection'] = dirname( __FILE__ ) . '/SpecialArticleProtection.php';
$wgSpecialPages['ArticleProtection'] = 'SpecialArticleProtection';

// To add or remove articles change the values in this array on your LocalSettings.php file.
// The constant values can be found on https://www.mediawiki.org/wiki/Manual:Namespace_constants
$articleProtectionNS = array( NS_MAIN, NS_USER, NS_PROJECT, NS_FILE, NS_TEMPLATE );

$wgResourceModules['ext.articleprotection.edit'] = array(
	'styles' => array( 'ArticleProtection.css' ),
	'scripts' => 'ArticleProtection.js',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ArticleProtection',
	'position' => 'top' // available since r85616
);

$wgResourceModules['ext.articleprotection.view'] = array(
	'styles' => array( 'ArticleProtection.css' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ArticleProtection',
	'position' => 'top' // available since r85616
);
