<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/_wdt/styles' => [[['_route' => '_wdt_stylesheet', '_controller' => 'web_profiler.controller.profiler::toolbarStylesheetAction'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/acces' => [[['_route' => 'app_acces_index', '_controller' => 'App\\Controller\\AccesController::index'], null, ['GET' => 0], null, false, false, null]],
        '/acces/new' => [[['_route' => 'app_acces_new', '_controller' => 'App\\Controller\\AccesController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/badge' => [[['_route' => 'app_badge_index', '_controller' => 'App\\Controller\\BadgeController::index'], null, ['GET' => 0], null, false, false, null]],
        '/badge/new' => [[['_route' => 'app_badge_new', '_controller' => 'App\\Controller\\BadgeController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/badgeuse' => [[['_route' => 'app_badgeuse_index', '_controller' => 'App\\Controller\\BadgeuseController::index'], null, ['GET' => 0], null, false, false, null]],
        '/badgeuse/new' => [[['_route' => 'app_badgeuse_new', '_controller' => 'App\\Controller\\BadgeuseController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/dashboard' => [[['_route' => 'app_dashboard', '_controller' => 'App\\Controller\\DashboardController::index'], null, null, null, false, false, null]],
        '/organisation' => [[['_route' => 'app_organisation_index', '_controller' => 'App\\Controller\\OrganisationController::index'], null, ['GET' => 0], null, false, false, null]],
        '/organisation/new' => [[['_route' => 'app_organisation_new', '_controller' => 'App\\Controller\\OrganisationController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/pointage' => [[['_route' => 'app_pointage_index', '_controller' => 'App\\Controller\\PointageController::index'], null, ['GET' => 0], null, false, false, null]],
        '/pointage/new' => [[['_route' => 'app_pointage_new', '_controller' => 'App\\Controller\\PointageController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\RegistrationController::register'], null, null, null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, null, null, false, false, null]],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, null, null, false, false, null]],
        '/service' => [[['_route' => 'app_service_index', '_controller' => 'App\\Controller\\ServiceController::index'], null, ['GET' => 0], null, false, false, null]],
        '/service/new' => [[['_route' => 'app_service_new', '_controller' => 'App\\Controller\\ServiceController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/user' => [[['_route' => 'app_user_index', '_controller' => 'App\\Controller\\UserController::index'], null, ['GET' => 0], null, false, false, null]],
        '/user/new' => [[['_route' => 'app_user_new', '_controller' => 'App\\Controller\\UserController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/zone' => [[['_route' => 'app_zone_index', '_controller' => 'App\\Controller\\ZoneController::index'], null, ['GET' => 0], null, false, false, null]],
        '/zone/new' => [[['_route' => 'app_zone_new', '_controller' => 'App\\Controller\\ZoneController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/acces/([^/]++)(?'
                    .'|(*:220)'
                    .'|/edit(*:233)'
                    .'|(*:241)'
                .')'
                .'|/badge(?'
                    .'|/([^/]++)(?'
                        .'|(*:271)'
                        .'|/edit(*:284)'
                        .'|(*:292)'
                    .')'
                    .'|use/([^/]++)(?'
                        .'|(*:316)'
                        .'|/edit(*:329)'
                        .'|(*:337)'
                    .')'
                .')'
                .'|/organisation/([^/]++)(?'
                    .'|(*:372)'
                    .'|/edit(*:385)'
                    .'|(*:393)'
                .')'
                .'|/pointage/([^/]++)(?'
                    .'|(*:423)'
                    .'|/edit(*:436)'
                    .'|(*:444)'
                .')'
                .'|/service/([^/]++)(?'
                    .'|(*:473)'
                    .'|/edit(*:486)'
                    .'|(*:494)'
                .')'
                .'|/user/([^/]++)(?'
                    .'|(*:520)'
                    .'|/edit(*:533)'
                    .'|(*:541)'
                .')'
                .'|/zone/([^/]++)(?'
                    .'|(*:567)'
                    .'|/edit(*:580)'
                    .'|(*:588)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        220 => [[['_route' => 'app_acces_show', '_controller' => 'App\\Controller\\AccesController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        233 => [[['_route' => 'app_acces_edit', '_controller' => 'App\\Controller\\AccesController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        241 => [[['_route' => 'app_acces_delete', '_controller' => 'App\\Controller\\AccesController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        271 => [[['_route' => 'app_badge_show', '_controller' => 'App\\Controller\\BadgeController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        284 => [[['_route' => 'app_badge_edit', '_controller' => 'App\\Controller\\BadgeController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        292 => [[['_route' => 'app_badge_delete', '_controller' => 'App\\Controller\\BadgeController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        316 => [[['_route' => 'app_badgeuse_show', '_controller' => 'App\\Controller\\BadgeuseController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        329 => [[['_route' => 'app_badgeuse_edit', '_controller' => 'App\\Controller\\BadgeuseController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        337 => [[['_route' => 'app_badgeuse_delete', '_controller' => 'App\\Controller\\BadgeuseController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        372 => [[['_route' => 'app_organisation_show', '_controller' => 'App\\Controller\\OrganisationController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        385 => [[['_route' => 'app_organisation_edit', '_controller' => 'App\\Controller\\OrganisationController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        393 => [[['_route' => 'app_organisation_delete', '_controller' => 'App\\Controller\\OrganisationController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        423 => [[['_route' => 'app_pointage_show', '_controller' => 'App\\Controller\\PointageController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        436 => [[['_route' => 'app_pointage_edit', '_controller' => 'App\\Controller\\PointageController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        444 => [[['_route' => 'app_pointage_delete', '_controller' => 'App\\Controller\\PointageController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        473 => [[['_route' => 'app_service_show', '_controller' => 'App\\Controller\\ServiceController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        486 => [[['_route' => 'app_service_edit', '_controller' => 'App\\Controller\\ServiceController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        494 => [[['_route' => 'app_service_delete', '_controller' => 'App\\Controller\\ServiceController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        520 => [[['_route' => 'app_user_show', '_controller' => 'App\\Controller\\UserController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        533 => [[['_route' => 'app_user_edit', '_controller' => 'App\\Controller\\UserController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        541 => [[['_route' => 'app_user_delete', '_controller' => 'App\\Controller\\UserController::delete'], ['id'], ['POST' => 0], null, false, true, null]],
        567 => [[['_route' => 'app_zone_show', '_controller' => 'App\\Controller\\ZoneController::show'], ['id'], ['GET' => 0], null, false, true, null]],
        580 => [[['_route' => 'app_zone_edit', '_controller' => 'App\\Controller\\ZoneController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        588 => [
            [['_route' => 'app_zone_delete', '_controller' => 'App\\Controller\\ZoneController::delete'], ['id'], ['POST' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
