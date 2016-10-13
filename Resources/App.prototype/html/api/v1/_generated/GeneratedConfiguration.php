<?php
/**
* Generated by Flexions for benoit@pereira-da-silva.com on ?
* https://github.com/benoit-pereira-da-silva/Flexions/
*
* DO NOT MODIFY THIS FILE YOUR MODIFICATIONS WOULD BE ERASED ON NEXT GENERATION!
* IF NECESSARY YOU CAN MARK THIS FILE TO BE PRESERVED
* IN THE PREPROCESSOR BY ADDING IN Hypotypose::instance().preservePath
*
* Copyright (c) 2015  LyLo Media group  All rights reserved.
*/

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

require_once BARTLEBY_ROOT_FOLDER . 'Commons/_generated/BartlebyCommonsConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';

use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;

class GeneratedConfiguration extends BartlebyCommonsConfiguration {


    protected function _configurePermissions(){

        $permissionsRules = array(
		'ReadMessageById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateMessage->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateMessage->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteMessage->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateMessages->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadMessagesByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateMessages->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteMessages->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadMessagesByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadWorkspaceById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateWorkspace->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateWorkspace->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteWorkspace->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateWorkspaces->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadWorkspacesByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateWorkspaces->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteWorkspaces->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadWorkspacesByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadProjectById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateProject->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateProject->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteProject->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateProjects->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadProjectsByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateProjects->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteProjects->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadProjectsByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadEpisodeById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateEpisode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateEpisode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteEpisode->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateEpisodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadEpisodesByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateEpisodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteEpisodes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadEpisodesByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadFragmentById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateFragment->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateFragment->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteFragment->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'CreateFragments->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadFragmentsByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'UpdateFragments->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'DeleteFragments->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
		'ReadFragmentsByQuery->call'=>array('level' => PERMISSION_BY_IDENTIFICATION)
      );
        $this->addPermissions($permissionsRules);
    }

/*
    In your Configuration you can override the aliases.

    protected function _getPagesRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        ''=>'Start',
        'time'=>'Time',
        '*' => 'NotFound'
        );
        $routes->addAliasesToMapping($mapping);
    return $routes;
    }

    protected function _getEndPointsRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        'POST:/user/{userId}/comments'=>array('CommentsByUser','POST_method_for_demo'),
        'DELETE:/user/{userId}/comments'=>array('CommentsByUser','DELETE'),
        'time'=>'SSETime' // A server sent event sample
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }


*/

    protected function _getEndPointsRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
			'GET:/message/{messageId}'=>array('ReadMessageById','call'),
			'POST:/message'=>array('CreateMessage','call'),
			'PUT:/message'=>array('UpdateMessage','call'),
			'DELETE:/message'=>array('DeleteMessage','call'),
			'POST:/messages'=>array('CreateMessages','call'),
			'GET:/messages'=>array('ReadMessagesByIds','call'),
			'PUT:/messages'=>array('UpdateMessages','call'),
			'DELETE:/messages'=>array('DeleteMessages','call'),
			'POST:/messagesByQuery'=>array('ReadMessagesByQuery','call'),
			'GET:/workspace/{workspaceId}'=>array('ReadWorkspaceById','call'),
			'POST:/workspace'=>array('CreateWorkspace','call'),
			'PUT:/workspace'=>array('UpdateWorkspace','call'),
			'DELETE:/workspace'=>array('DeleteWorkspace','call'),
			'POST:/workspaces'=>array('CreateWorkspaces','call'),
			'GET:/workspaces'=>array('ReadWorkspacesByIds','call'),
			'PUT:/workspaces'=>array('UpdateWorkspaces','call'),
			'DELETE:/workspaces'=>array('DeleteWorkspaces','call'),
			'POST:/workspacesByQuery'=>array('ReadWorkspacesByQuery','call'),
			'GET:/project/{projectId}'=>array('ReadProjectById','call'),
			'POST:/project'=>array('CreateProject','call'),
			'PUT:/project'=>array('UpdateProject','call'),
			'DELETE:/project'=>array('DeleteProject','call'),
			'POST:/projects'=>array('CreateProjects','call'),
			'GET:/projects'=>array('ReadProjectsByIds','call'),
			'PUT:/projects'=>array('UpdateProjects','call'),
			'DELETE:/projects'=>array('DeleteProjects','call'),
			'POST:/projectsByQuery'=>array('ReadProjectsByQuery','call'),
			'GET:/episode/{episodeId}'=>array('ReadEpisodeById','call'),
			'POST:/episode'=>array('CreateEpisode','call'),
			'PUT:/episode'=>array('UpdateEpisode','call'),
			'DELETE:/episode'=>array('DeleteEpisode','call'),
			'POST:/episodes'=>array('CreateEpisodes','call'),
			'GET:/episodes'=>array('ReadEpisodesByIds','call'),
			'PUT:/episodes'=>array('UpdateEpisodes','call'),
			'DELETE:/episodes'=>array('DeleteEpisodes','call'),
			'POST:/episodesByQuery'=>array('ReadEpisodesByQuery','call'),
			'GET:/fragment/{fragmentId}'=>array('ReadFragmentById','call'),
			'POST:/fragment'=>array('CreateFragment','call'),
			'PUT:/fragment'=>array('UpdateFragment','call'),
			'DELETE:/fragment'=>array('DeleteFragment','call'),
			'POST:/fragments'=>array('CreateFragments','call'),
			'GET:/fragments'=>array('ReadFragmentsByIds','call'),
			'PUT:/fragments'=>array('UpdateFragments','call'),
			'DELETE:/fragments'=>array('DeleteFragments','call'),
			'POST:/fragmentsByQuery'=>array('ReadFragmentsByQuery','call')
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }
}
?>