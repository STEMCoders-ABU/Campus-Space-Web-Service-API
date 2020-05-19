<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->group('1.0', function($routes)
{
	$routes->group('provider', function($routes)
	{
		$routes->get('faculties', 'Provider::get_faculties');
		$routes->get('departments', 'Provider::get_departments');
		$routes->get('levels', 'Provider::get_levels');
		$routes->get('courses', 'Provider::get_courses');
	});

	$routes->group('news', function($routes)
	{
		$routes->get('show', 'News::show');
		$routes->get('comments', 'News::show_comments');
		$routes->post('comments', 'News::add_comment');
		$routes->get('comments/category', 'News::show_category_comments');
		$routes->post('comments/category', 'News::add_category_comment');
	});

	$routes->group('moderator/(:segment)', function($routes)
	{
		$routes->get('show', 'Moderator::show/$1');
		$routes->post('update', 'Moderator::update/$1');
		$routes->get('courses', 'Moderator::courses/$1');
		$routes->get('categories', 'Moderator::categories/$1');
		$routes->post('resource', 'Moderator::add_resource/$1');
		$routes->post('news', 'Moderator::add_news/$1');
		$routes->get('resources', 'Moderator::get_resources/$1');
		$routes->get('news', 'Moderator::get_news/$1');
		$routes->delete('resource/(:segment)', 'Moderator::delete_resource/$1/$2');
		$routes->delete('news/(:segment)', 'Moderator::delete_news/$1/$2');
		$routes->get('resource/(:segment)', 'Moderator::get_resource/$1/$2');
		$routes->get('news/(:segment)', 'Moderator::get_news_item/$1/$2');
		$routes->post('resource/(:segment)', 'Moderator::update_resource/$1/$2');
		$routes->post('news/(:segment)', 'Moderator::update_news/$1/$2');
		$routes->post('course', 'Moderator::add_course/$1');
	});

	$routes->group('resources', function($routes)
	{
		$routes->get('show', 'Resources::show');
		$routes->get('comments', 'Resources::show_comments');
		$routes->post('comments', 'Resources::add_comment');
		$routes->get('comments/category', 'Resources::show_category_comments');
		$routes->post('comments/category', 'Resources::add_category_comment');
	});

	$routes->group('news', function($routes)
	{
		$routes->get('show', 'News::show');
		$routes->get('comments', 'News::show_comments');
		$routes->post('comments', 'News::add_comment');
		$routes->get('comments/category', 'News::show_category_comments');
		$routes->post('comments/category', 'News::add_category_comment');
	});
});


/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
