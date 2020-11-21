<?php
namespace App\Controllers;

\define ('RESOURCES_PATH', 'public/resources/files/');
\define('IN_DEVELOPMENT', TRUE);

use CodeIgniter\Controller;

class BaseController extends Controller
{
	protected $helpers = ['text'];

	protected $session;
	protected $validation;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}

	public function options()
    {
        $this->setDefaultHeaders();
        return $this->response;
	}
	
	protected function setDefaultHeaders() 
	{
	    try {
			$origin = $this->request->getHeader('Origin');
			if (!$origin)
				return;

			$origin = $origin->getValue();
			$allowed_origin = '';
			
			$main_domain = 'thrifty.com';
			if ($origin == ('https://' . $main_domain) || $origin == ('https://www.' . $main_domain))
				$allowed_origin = $origin;
				
			if (IN_DEVELOPMENT)
				$allowed_origin = $origin;

			$this->response->setHeader('Access-Control-Allow-Origin', $allowed_origin);
			$this->response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, *');
			$this->response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, PATCH, PUT');
			$this->response->setHeader('Access-Control-Allow-Credentials', 'true');
		} catch (Throwable $th) {
			// We do nothing. If the headers are not provided then access will be denied!
		}
	}

	protected function setDefaultDownloadHeaders($response) 
	{
	    try {
			$origin = $this->request->getHeader('Origin');
			if (!$origin)
				return;

			$origin = $origin->getValue();
			$allowed_origin = '';
			
			$main_domain = 'thrifty.com';
			if ($origin == ('https://' . $main_domain) || $origin == ('https://www.' . $main_domain))
				$allowed_origin = $origin;
				
			if (IN_DEVELOPMENT)
				$allowed_origin = $origin;

			$response->setHeader('Access-Control-Allow-Origin', $allowed_origin);
			$response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, *');
			$response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, DELETE, PATCH, PUT');
			$response->setHeader('Access-Control-Allow-Credentials', 'true');
		} catch (Throwable $th) {
			// We do nothing. If the headers are not provided then access will be denied!
		}
	}

	/* Converts an array of strings to a single string. It uses newline (\n) as a separator. */
	protected function errorArrayToString($array) 
	{
		$str = '';
		foreach ($array as $item) {
			$str .= $item . '\n';
		}

		return $str;
	}

	/* Converts an array of strings to a single string. It uses newline (\n) as a separator. Fallback for older versions. */
	protected function array_to_string($array) 
	{
		$str = '';
		foreach ($array as $item) {
			$str .= $item . '\n';
		}

		return $str;
	}

	/* Logs an exception. */
	protected function logException($exception, $extraMessage = '') 
	{
		log_message('error', '[ERROR] {exception}', ['exception' => $exception]);
		
		if ($extraMessage)
			log_message('EXTRA MESSAGE: ' . $extraMessage);
	}

	/* Logs exceptions that are not expected to occcur. This method should take steps necessary to alert the site administrators ASAP. */
	protected function logUnexpectedException($message) 
	{
		log_message('error', '[UNEXPECTED EXCEPTION] ' . $message);
	}
}