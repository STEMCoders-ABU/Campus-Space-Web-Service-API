<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Moderator extends BaseController 
{
    use ResponseTrait;
	
	protected $moderator_session_name;

	public function __construct()
	{
		$this->moderator_session_name = 'moderation_session_data';
	}

	public function show() 
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            $moderator = $this->authenticateSession();
		
			if ($moderator)
			{
				unset($moderator['id']);
				unset($moderator['password']);
				return $this->respond($moderator, 200);
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function createSession() 
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticate();

            if ($moderator) {
				$this->session->set($this->moderator_session_name, $moderator);
				return $this->respondNoContent();
            }
            else {
                // Authentication failed
                return $this->failUnauthorized('Authentication failed!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function verifySession() 
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();

            if ($this->session->has($this->moderator_session_name)) {
                // A valid session exists
                return $this->respondNoContent('Session verified');
            }
            else {
                // Invalid or expired session
                // Clear any session data that exists
                $this->session->remove($this->moderator_session_name);

                return $this->failUnauthorized('Session verification failed!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

	public function clearSession() 
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();

            // Clear the current session
            $this->session->destroy();
            return $this->respondDeleted('Session cleared');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}
	
	public function update()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$fields = $this->request->getJSON(true);

				if (! $fields)
					return $this->failNotFound('No input data was provided!');

				if (isset($fields['email']))
					$validationRules['email'] = "required|max_length[50]|valid_email|is_unique[moderators.email,email,{$moderator['email']}]";
					
				if (isset($fields['password']))
					$validationRules['password'] = 'required|max_length[70]';
				
				if (isset($fields['full_name']))
					$validationRules['full_name'] = 'required|min_length[2]|max_length[50]';
				
				if (isset($fields['gender']))
					$validationRules['gender'] = 'required|min_length[4]|max_length[6]';
				
				if (isset($fields['phone']))
					$validationRules['phone'] = 'required|min_length[11]|max_length[15]';
					
				if (! isset($validationRules))
					return $this->failNotFound('No valid input data was provided!');

				$this->validation->setRules($validationRules);
				if (! $this->validation->run($fields)) {
					return $this->failValidationError($this->array_to_string($this->validation->getErrors()));
				}

				$moderatorsModel = \model('App\Models\ModeratorsModel', true);
				$id = $moderator['id'];

				if (isset($fields['password']))
					$fields['password'] = password_hash($fields['password'], PASSWORD_DEFAULT);

				if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
					$join = TRUE;
				else
					$join = FALSE;

				if ($moderatorsModel->update($id, $fields)) {
					$data = $moderatorsModel->getModeratorData($moderator['username'], $join);
					return $this->respond($data, 200);
				}
				else {
					return $this->fail('Failed to update moderator!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function courses()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$moderatorsModel = \model('App\Models\ModeratorsModel', true);
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
				$constraints = [
					'department_id' => $department_id,
					'level_id' => $level_id,
				];

				if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
					$join = TRUE;
				else
					$join = FALSE;

				if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
					$size = $this->request->getGet('size');
				else
					$size = 0;

				if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
					$offset = $this->request->getGet('offset');
				else
					$offset = 0;

				$courses = $moderatorsModel->getCourses($constraints, $size, $offset, $join);
				if ($courses) {
					return $this->respond($courses, 200);
				}
				else {
					return $this->failNotFound('No courses are registered for this moderator yet!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function add_resource()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$fields = $this->request->getPost();

				if (! $fields)
					return $this->failNotFound('No input data was provided!');

				$allowed_types = '';
				$category_id = $this->request->getPost('category_id');
				if (! $category_id)
					return $this->failNotFound('No category_id was provided!');

				$course_id = $this->request->getPost('course_id');
				if (! $course_id)
					return $this->failNotFound('No course_id was provided!');
				
				if ($category_id == 1)
					$allowed_types = 'pdf';
				else if ($category_id == 2)
					$allowed_types = '3gpp,mp4';
				else if ($category_id == 3)
					$allowed_types = 'pdf';
				else if ($category_id == 4)
					$allowed_types = 'dot,doc,docx,dotx,docm,xls,xlsx,ppt,pptx';

				$file_size = 0;
				if ($category_id == 1)
					$file_size = 51200;
				else if ($category_id == 2)
					$file_size = 153600;
				else if ($category_id == 3)
					$file_size = 51200;
				else if ($category_id == 4)
					$file_size = 51200;

				$validationRules = [
					'title' => 'required|max_length[50]|is_unique[resources.title]',
					'description' => 'required|max_length[2000]',
					'category_id' => 'required|is_not_unique[resource_categories.id]',
					'course_id' => 'required|is_not_unique[courses.id]',
					'file' => "uploaded[file]|max_size[file,{$file_size}]|ext_in[file,{$allowed_types}]",
				];

				if (! $this->validate($validationRules)) {
					return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
				}

				$faculty_id = $moderator['faculty_id'];
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];

				$file = $this->request->getFile('file');
				$file_name = strtolower(\url_title( $this->request->getPost('title'))) . '.' . $file->getExtension();

				$moderatorsModel = \model('App\Models\ModeratorsModel', true);
				$entries = [
					'title' => $this->request->getPost('title'),
					'description' => $this->request->getPost('description'),
					'course_id' => $course_id,
					'category_id' => $category_id,
					'faculty_id' => $faculty_id,
					'department_id' => $department_id,
					'level_id' => $level_id,
					'file' => $file_name,
				];

				if ($moderatorsModel->add_resource($entries)) {
					$file->move(RESOURCES_PATH, $file_name);
					return $this->respond([], 200);
				}
				else {
					return $this->fail('Failed to add resource!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function get_resources()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
				$constraints = [
					'resources.department_id' => $department_id,
					'resources.level_id' => $level_id,
				];

				if ($this->request->getGet('category_id') && is_numeric($this->request->getGet('category_id')))
					$constraints['resources.category_id'] = $this->request->getGet('category_id');

				if ($this->request->getGet('course_id') && is_numeric($this->request->getGet('course_id')))
					$constraints['resources.course_id'] = $this->request->getGet('course_id');

				if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
					$join = TRUE;
				else
					$join = FALSE;

				if ($this->request->getGet('order_by_downloads') && $this->request->getGet('order_by_downloads') == 'true')
					$by_downloads = TRUE;
				else
					$by_downloads = FALSE;

				if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
					$offset = $this->request->getGet('offset');
				else
					$offset = 0;

				if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
					$size = $this->request->getGet('size');
				else {
					if ($by_downloads)
						$size = 10;
					else
						$size = 0;
				}

				$resourcesModel = \model('App\Models\ResourcesModel', true);

				if ($by_downloads)
					$data = $resourcesModel->getResourcesByDownloads($constraints, $size, $offset, $join);
				else
					$data = $resourcesModel->getResources($constraints, $size, $offset, $join);

				if ($data) {
					return $this->respond($data, 200);
				}
				else {
					return $this->failNotFound('No resources uploaded by this moderator yet!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function delete_resource()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$resource_id = $this->request->getGet('resource_id');
				if ($resource_id == null)
					return $this->failNotFound('No resource_id was provided!');

				$moderatorsModel = \model('App\Models\ModeratorsModel', true);
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
				$constraints = [
					'id' => $resource_id,
					'department_id' => $department_id,
					'level_id' => $level_id,
				];

				$resourcesModel = \model('App\Models\ResourcesModel', true);
				$resource = $resourcesModel->get_resource_item($constraints);
				if ($moderatorsModel->remove_resource($constraints)) {
					$file_deleted = unlink(RESOURCES_PATH . $resource['file']);
					if ($file_deleted)
						$file_deleted = 'true';
					else
						$file_deleted = 'false';

					return $this->respond(['file_deleted' => $file_deleted], 200);
				}
				else {
					return $this->fail('Failed to delete resource!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function get_resource()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$resource_id = $this->request->getGet('resource_id');
				if ($resource_id == null)
					return $this->failNotFound('No resource_id was provided!');
				
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
				$constraints = [
					'resources.id' => $resource_id,
					'resources.department_id' => $department_id,
					'resources.level_id' => $level_id,
				];

				if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
					$join = TRUE;
				else
					$join = FALSE;

				$resourcesModel = \model('App\Models\ResourcesModel', true);
				$data = $resourcesModel->get_resource_item($constraints, $join);
				if ($data) {
					return $this->respond($data, 200);
				}
				else {
					return $this->failNotFound('The requested resource does not exist for this moderator!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function update_resource()
	{
		try {
            // Set the headers
			$this->setDefaultHeaders();
			
			$moderator = $this->authenticateSession();
		
			if ($moderator) {
				$resource_id = $this->request->getGet('resource_id');
				if ($resource_id == null)
					return $this->failNotFound('No resource_id was provided!');
	
				$fields = $this->request->getJSON(true);
	
				if (! $fields)
					return $this->failNotFound('No input data was provided!');
	
				$faculty_id = $moderator['faculty_id'];
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
	
				$constraints = [
					'id' => $resource_id,
					'faculty_id' => $faculty_id,
					'department_id' => $department_id,
					'level_id' => $level_id,
				];
	
				$resourcesModel = \model('App\Models\ResourcesModel', true);
				$resource = $resourcesModel->get_resource_item($constraints);
				if (! $resource)
					return $this->failNotFound('The requested resource was not found! The resource probably does not belong to this moderator.');
	
				$current_title = $resource['title'];
				
				if (isset($fields['title']))
					$validationRules['title'] = "required|max_length[50]|is_unique[resources.title,title,{$current_title}]";
					
				if (isset($fields['description']))
					$validationRules['description'] = 'required|max_length[2000]';
	
				if (isset($fields['course_id']))
					$validationRules['course_id'] = 'required|is_not_unique[courses.id]';
	
				if (! isset($validationRules))
					return $this->failNotFound('No valid input data was provided!');
	
				$this->validation->setRules($validationRules);
				if (! $this->validation->run($fields)) {
					return $this->failValidationError($this->array_to_string($this->validation->getErrors()));
				}
	
				if ($resourcesModel->update_resource($resource_id, $fields)) {
					return $this->respond([], 200);
				}
				else {
					return $this->fail('Failed to update resource!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function add_course()
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $moderator = $this->authenticateSession();
		
			if ($moderator) {
				$fields = $this->request->getJSON(true);

				if (! $fields)
					return $this->failNotFound('No input data was provided!');

				$validationRules = [
					'course_title' => 'required|max_length[60]|is_unique[courses.course_title]',
					'course_code' => 'required|max_length[12]|is_unique[courses.course_code]',
				];

				$this->validation->setRules($validationRules);
				if (! $this->validation->run($fields)) {
					return $this->failValidationError($this->array_to_string($this->validation->getErrors()));
				}

				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];

				$moderatorsModel = \model('App\Models\ModeratorsModel', true);
				$entries = [
					'course_title' => $this->request->getPost('course_title'),
					'course_code' => $this->request->getPost('course_code'),
					'department_id' => $department_id,
					'level_id' => $level_id,
				];

				if ($moderatorsModel->add_course($entries)) {
					return $this->respond([], 200);
				}
				else {
					return $this->fail('Failed to add course!');
				}
			}
			else
				return $this->failUnauthorized('Authentication failed!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	private function authenticate() {
		if (! $this->request->hasHeader('Authorization'))
			return FALSE;

		$credentials = $this->request->getHeader('Authorization')->getValue();
		$credentials = \explode(' ', $credentials);

		if (\count($credentials) < 2)
			return FALSE;

		if ($credentials[0] !== 'Basic')
			return FALSE;
		
		$credentials = $credentials[1];
		$credentials = \base64_decode($credentials, TRUE);

		if (! $credentials)
			return FALSE;

		$credentials = \explode(':', $credentials);

		if (\count($credentials) < 2)
			return FALSE;

		$username = $credentials[0];
		$password = $credentials[1];

		$moderatorsModel = \model('App\Models\ModeratorsModel', true);

		if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
			$join = FALSE;
			
		$data = $moderatorsModel->getModeratorData($username, $join);

		if (! $data)
			return FALSE;

		if (password_verify($password, $data['password']))
			return $data;
		else
			return FALSE;
	}

	protected function authenticateSession() {
        // Check if a valid session exists
        if ($this->session->has($this->moderator_session_name)) {
            $data = $this->session->get($this->moderator_session_name);
            return $data;
		}
        else {
			// No session data found. Let's try other authentication method(s)
			// We can authenticate if a BASIC AUTH header was provided.
			return $this->authenticate();
		}
    }
}