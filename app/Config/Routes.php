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

$routes->options('(:any)', 'BaseController::options');

$routes->group('1.0', function($routes)
{
	$routes->group('provider', function($routes)
	{
		$routes->get('faculties', 'Provider::get_faculties');
		$routes->get('departments', 'Provider::get_departments');
		$routes->get('levels', 'Provider::get_levels');
		$routes->get('courses', 'Provider::get_courses');
		$routes->get('resource_categories', 'Provider::get_resource_categories');
		$routes->get('news_categories', 'Provider::get_news_categories');
	});

	$routes->get('moderator', 'Moderator::show');
	$routes->group('moderator', function($routes)
	{
		$routes->get('show', 'Moderator::show');
		$routes->post('update', 'Moderator::update');
		$routes->get('courses', 'Moderator::courses');
		$routes->post('resource', 'Moderator::add_resource');
		$routes->post('news_item', 'Moderator::add_news');
		$routes->get('resources', 'Moderator::get_resources');
		$routes->get('news', 'Moderator::get_news');
		$routes->delete('resource', 'Moderator::delete_resource');
		$routes->delete('news_item', 'Moderator::delete_news');
		$routes->get('resource', 'Moderator::get_resource');
		$routes->get('news_item', 'Moderator::get_news_item');
		$routes->post('resource/update', 'Moderator::update_resource');
		$routes->post('news_item/update', 'Moderator::update_news');
		$routes->post('course', 'Moderator::add_course');
	});

	$routes->get('resources', 'Resources::show');
	$routes->group('resources', function($routes)
	{
		$routes->get('show', 'Resources::show');
		$routes->get('resource', 'Resources::get_resource');
		$routes->post('search', 'Resources::search');
		$routes->get('comments', 'Resources::show_comments');
		$routes->post('comments', 'Resources::add_comment');
		$routes->get('comments/category', 'Resources::show_category_comments');
		$routes->post('comments/category', 'Resources::add_category_comment');
		$routes->get('download', 'Resources::download');
	});

	$routes->get('news', 'News::show');
	$routes->group('news', function($routes)
	{
		$routes->get('show', 'News::show');
		$routes->get('news_item', 'News::get_news_item');
		$routes->post('search', 'News::search');
		$routes->get('comments', 'News::show_comments');
		$routes->post('comments', 'News::add_comment');
		$routes->get('comments/category', 'News::show_category_comments');
		$routes->post('comments/category', 'News::add_category_comment');
	});
});

$routes->group('1.1', function($routes) {
	$routes->get('faculties', 'Provider::get_faculties');
	$routes->get('departments', 'Provider::get_departments');
	$routes->get('levels', 'Provider::get_levels');
	$routes->get('courses', 'Provider::get_courses');
	$routes->get('categories', 'Provider::get_resource_categories');
	$routes->get('moderator/public', 'Provider::get_moderator');
	$routes->get('stats', 'Provider::get_stats');
	$routes->get('app', 'Provider::downloadApp');
	
	$routes->group('moderator', function($routes) {
		$routes->get('', 'Moderator::show');
		$routes->put('', 'Moderator::update');
		$routes->get('courses', 'Moderator::courses');
		$routes->post('courses', 'Moderator::add_course');
		$routes->get('resources', 'Moderator::get_resources');
		
		$routes->group('resource', function($routes) {
			$routes->get('', 'Moderator::get_resource');
			$routes->post('', 'Moderator::add_resource');
			$routes->delete('', 'Moderator::delete_resource');
			$routes->put('', 'Moderator::update_resource');
		});

		$routes->group('session', function($routes) {
			$routes->get('', 'Moderator::verifySession');
			$routes->post('', 'Moderator::createSession');
			$routes->delete('', 'Moderator::clearSession');
		});

		$routes->group('password_reset', function($routes) {
			$routes->post('resend', 'Moderator::resendVerificationCode');
			$routes->post('', 'Moderator::initializePasswordReset');
			$routes->post('finalize', 'Moderator::finalizePasswordReset');
		});
	});

	$routes->group('admin', function($routes) {
		$routes->group('session', function($routes) {
			$routes->get('', 'Admin::verifySession');
			$routes->post('', 'Admin::createSession');
			$routes->delete('', 'Admin::clearSession');
		});

		$routes->group('faculty', function($routes) {
			$routes->post('', 'Admin::addFaculty');
			$routes->put('(:segment)', 'Admin::updateFaculty/$1');
			$routes->delete('(:segment)', 'Admin::removeFaculty/$1');
		});

		$routes->group('department', function($routes) {
			$routes->post('', 'Admin::addDepartment');
			$routes->put('(:segment)', 'Admin::updateDepartment/$1');
			$routes->delete('(:segment)', 'Admin::removeDepartment/$1');
		});

		$routes->group('moderator', function($routes) {
			$routes->post('', 'Admin::addModerator');
		});
	});

	$routes->group('resources', function($routes)
	{
		$routes->get('', 'Resources::show');
		$routes->get('resource', 'Resources::get_resource');
		$routes->post('search', 'Resources::search');
		$routes->get('download', 'Resources::download');
		$routes->post('subscription', 'Resources::addSubscription');
	});

	$routes->group('comments', function($routes)
	{
		$routes->get('', 'Resources::show_comments');
		$routes->post('', 'Resources::add_comment');
		$routes->get('category', 'Resources::show_category_comments');
		$routes->post('category', 'Resources::add_category_comment');
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
