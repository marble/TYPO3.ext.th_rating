<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$GLOBALS['TCA']['tx_thrating_domain_model_ratingobject'] = array(
	'ctrl' => array (
		'title'				=> 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.title',
		'label'				=> 'uid',
		'label_alt'			=> 'ratetable,ratefield',
 		'label_userFunc'	=> 'Thucke\\ThRating\\Userfuncs\\Tca->getRatingObjectRecordTitle',
		'tstamp'			=> 'tstamp',
		'crdate'			=> 'crdate',
		'cruser_id'			=> 'cruser_id',
		'delete'			=> 'deleted',
		'enablecolumns'		=> array(
			'disabled'	=> 'hidden'
			),
		'iconfile' 			=> 'EXT:th_rating/Resources/Public/Icons/tx_thrating_domain_model_ratingobject.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden, ratetable, ratefield'
	),
	'columns' => array(
		'pid' => array(
			'exclude' => 1,
			'config' => array(
				'type' => 'none',
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type' => 'check'
			)
		),
		'ratetable' => array(
			'exclude' => 0,
			'label'   => 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.ratetable',
			'config'  => array(
				'type' => 'input',
				'size' => 20,
				'eval' => 'trim,required',
				'max'  => 64
			)
		),
		'ratefield' => array(
			'exclude' => 0,
			'label'   => 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.ratefield',
			'config'  => array(
				'type' => 'input',
				'size' => 20,
				'eval' => 'trim,required',
				'max'  => 64
					)
		),
		'uid' => array(
			'label'   => 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.uid',
		),
		'stepconfs' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.stepconfs',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_thrating_domain_model_stepconf',
				'foreign_field' => 'ratingobject',
				'foreign_default_sortby' => 'steporder',
				'maxitems'      => 999999,
				'appearance' => array(
					'levelLinksPosition' 	=> 'bottom',
					'collapseAll' 			=> true,
					'expandSingle' 			=> true,
					'newRecordLinkAddTitle' => true,
					//'newRecordLinkPosition' => 'both',
					//'showSynchronizationLink' => true,
					//'showAllLocalizationLink' => true,
					//'showPossibleLocalizationRecords' => 1,
					//'showRemovedLocalizationRecords' => 1,
				),
			),
		),
		'ratings' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:th_rating/Resources/Private/Language/locallang.xlf:tca.model.ratingobject.ratings',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_thrating_domain_model_rating',
				'foreign_field' => 'ratingobject',
				'foreign_default_sortby' => 'uid',
				'maxitems'      => 999999,
				'appearance' => array(
					'levelLinksPosition' => 'bottom',
					'collapseAll' => 1,
					'expandSingle' => 1,
					'newRecordLinkAddTitle' => 1,
					'newRecordLinkPosition' => 'both',
				),
			),
		),
	),
	'types' => array(
		'1' => array('showitem' => 'hidden, ratetable, ratefield, stepconfs, ratings')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	),
);
return $GLOBALS['TCA']['tx_thrating_domain_model_ratingobject'];
?>