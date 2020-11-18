<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Moderator extends BaseController 
{
    use ResponseTrait;
	
	protected $moderator_session_name;

	public function __construct()
	{
		$this->moderator_session_name = 'moderation_session_data';
		$this->model = \model('App\Models\ModeratorsModel', true);
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
				
				if (!$fields)
					$fields = $this->request->getPost();

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
					return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
				}

				
				$id = $moderator['id'];

				if (isset($fields['password']))
					$fields['password'] = password_hash($fields['password'], PASSWORD_DEFAULT);

				if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
					$join = TRUE;
				else
					$join = FALSE;

				if ($this->model->update($id, $fields)) {
					$data = $this->model->getModeratorData($moderator['username'], $join);
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

				$courses = $this->model->getCourses($constraints, $size, $offset, $join);
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
					return $this->failValidationError($this->errorArrayToString($this->validator->getErrors()));
				}

				$faculty_id = $moderator['faculty_id'];
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];

				$file = $this->request->getFile('file');
				$file_name = strtolower(\url_title( $this->request->getPost('title'))) . '.' . $file->getExtension();

				
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

				if ($this->model->add_resource($entries)) {
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

				
				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];
				$constraints = [
					'id' => $resource_id,
					'department_id' => $department_id,
					'level_id' => $level_id,
				];

				$resourcesModel = \model('App\Models\ResourcesModel', true);
				$resource = $resourcesModel->get_resource_item($constraints);
				if ($this->model->remove_resource($constraints)) {
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
				
				if (!$fields)
					$fields = $this->request->getPost();

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
					return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
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
				
				if (!$fields)
					$fields = $this->request->getPost();

				if (! $fields)
					return $this->failNotFound('No input data was provided!');

				$validationRules = [
					'course_title' => 'required|max_length[60]|is_unique[courses.course_title]',
					'course_code' => 'required|max_length[12]|is_unique[courses.course_code]',
				];

				$this->validation->setRules($validationRules);
				if (! $this->validation->run($fields)) {
					return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
				}

				$department_id = $moderator['department_id'];
				$level_id = $moderator['level_id'];

				
				$entries = [
					'course_title' => $this->request->getPost('course_title'),
					'course_code' => $this->request->getPost('course_code'),
					'department_id' => $department_id,
					'level_id' => $level_id,
				];

				if ($this->model->add_course($entries)) {
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

	public function initializePasswordReset() 
    {
        try {
			// Set the headers
			$this->setDefaultHeaders();
			
            $data = $this->request->getJSON(true);
            if (!$data)
                return $this->failNotFound('No valid data was provided!');

            $validationRules = ['email' => 'required|trim|valid_email|max_length[50]|is_not_unique[moderators.email]'];

            // validate
            $this->validation->setRules($validationRules);
            if ($this->validation->run($data))
            {
                // Make sure there is no existing password reset
                if ($this->model->getPasswordResetByEmail($data['email']))
                    return $this->failResourceExists('An unfinished password reset request exists!');

                // Generate verfication link
                $verification_code = \random_string('alnum', 32);

                // Add to database
                $entries = [
                    'email' => $data['email'],
                    'verification_code' => $verification_code,
                ];

                if ($this->model->addPasswordReset($entries)){
                    // get the client
                    $moderator = $this->model->getModeratorDataByEmail($data['email']);

                    // Email the recipient
                    $email = \Config\Services::email();
                    $email->setTo($data['email']);

                    $email->setSubject('Campus Space Password Reset');
                    $email->setMessage($this->getPassResetEmailBody($moderator['full_name'], $verification_code));

                    if (!$email->send())
                        return $this->fail('Failed to send verification code');
                    else
                        return $this->respondNoContent('Verification code sent.');
                }
                else
                    return $this->fail('An internal error occured', 500);
            }
            else // failed validation
                return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function verifyPasswordReset() 
    {
        try {
			// Set the headers
			$this->setDefaultHeaders();

            $data = $this->request->getJSON(true);
            if (!$data)
                return $this->failNotFound('No valid data was provided!');

            $validationRules = [
                'verification_code' => 'required|trim|is_not_unique[password_resets.verification_code]',
                'email' => 'required|trim|valid_email|max_length[50]|is_not_unique[moderators.email]',
            ];

            // validate
            $this->validation->setRules($validationRules);
            if ($this->validation->run($data)) {
                // If the validation is passed then the verification code exists

                // Get the reset data
                $reset = $this->model->getPasswordReset($data['verification_code']);

                // The data passed is only valid if the email coresponds!
                if ($data['email'] == $reset['email'])
                    return $this->respondNoContent('Verification code verified.');
                else
                    return $this->failValidationError('Invalid email');
            }
            else // failed validation
                return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function finalizePasswordReset() 
    {
        try {
			// Set the headers
			$this->setDefaultHeaders();

            $data = $this->request->getJSON(true);
            if (!$data)
                return $this->failNotFound('No valid data was provided!');

            $validationRules = [
                'verification_code' => 'required|trim|is_not_unique[password_resets.verification_code]',
                'email' => 'required|trim|valid_email|max_length[50]|is_not_unique[moderators.email]',
                'new_password' => 'required|trim',
                'confirm_password' => 'required|trim|matches[new_password]',
            ];

            // validate
            $this->validation->setRules($validationRules);
            if ($this->validation->run($data)) {
                // Get the reset data
                $reset = $this->model->getPasswordReset($data['verification_code']);

                // The data passed is only valid if the email coresponds!
                if ($data['email'] == $reset['email']) {
                    // Get the client
                    $moderator = $this->model->getModeratorDataByEmail($reset['email']);

                    // Update password
                    $password = password_hash($data['new_password'], PASSWORD_DEFAULT);

                    $entries = [
                        'password' => $password,
                    ];

                    if ($this->model->deletePasswordReset($reset['id']) && 
                        $this->model->update($moderator['id'], $entries))
                        return $this->respondNoContent('Password updated');
                    else
                        return $this->fail('Failed to reset password');
                }
                else
                    return $this->failValidationError('Invalid email');
            }
            else // failed validation
                return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function resendVerificationCode() 
    {
        try {
			// Set the headers
			$this->setDefaultHeaders();

            $data = $this->request->getJSON(true);
            if (!$data)
                return $this->failNotFound('No valid data was provided!');

            $validationRules = [
                'email' => 'required|trim|valid_email|max_length[50]|is_not_unique[password_resets.email]',
            ];

            // validate
            $this->validation->setRules($validationRules);
            if ($this->validation->run($data))
            {
                // If the validation is passed then the reset request exists

                // Get the reset data
                $reset = $this->model->getPasswordResetByEmail($data['email']);

                // get the client
                $moderator = $this->model->getModeratorDataByEmail($reset['email']);

                // Resend the verification code
                $email = \Config\Services::email();
                $email->setTo($data['email']);
                $email->setSubject('Campus Space Password Reset');
                $email->setMessage($this->getPassResetEmailBody($moderator['full_name'], $reset['verification_code']));

                if (!$email->send())
                    return $this->fail('Failed to resend verification code');
                else
                    return $this->respondNoContent('Verification code resent.');
            }
            else // failed validation
                return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
	}
	
	private function authenticate() 
	{
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

		

		if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
			$join = FALSE;
			
		$data = $this->model->getModeratorData($username, $join);

		if (! $data)
			return FALSE;

		if (password_verify($password, $data['password']))
			return $data;
		else
			return FALSE;
	}

	protected function authenticateSession() 
	{
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
	
	private function getPassResetEmailBody($full_name, $verification_code) 
	{
		$year = \date('Y');
		$reset_link = 'https://campus-space.com.ng/moderation/password_reset?code=' . $verification_code;
		$fb_img = \site_url('public/images/facebook2x.png');
		$tw_img = \site_url('public/images/twitter2x.png');

		return <<< EMAIL
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
		<head>
		<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
		<meta content="width=device-width" name="viewport"/>
		<!--[if !mso]><!-->
		<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
		<!--<![endif]-->
		<title></title>
		<!--[if !mso]><!-->
		<link href="https://fonts.googleapis.com/css?family=Abril+Fatface" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Bitter" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Cabin" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css"/>
		<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css"/>
		<!--<![endif]-->
		<style type="text/css">
				body {
					margin: 0;
					padding: 0;
				}

				table,
				td,
				tr {
					vertical-align: top;
					border-collapse: collapse;
				}

				* {
					line-height: inherit;
				}

				a[x-apple-data-detectors=true] {
					color: inherit !important;
					text-decoration: none !important;
				}
			</style>
		<style id="media-query" type="text/css">
				@media (max-width: 700px) {

					.block-grid,
					.col {
						min-width: 320px !important;
						max-width: 100% !important;
						display: block !important;
					}

					.block-grid {
						width: 100% !important;
					}

					.col {
						width: 100% !important;
					}

					.col_cont {
						margin: 0 auto;
					}

					img.fullwidth,
					img.fullwidthOnMobile {
						max-width: 100% !important;
					}

					.no-stack .col {
						min-width: 0 !important;
						display: table-cell !important;
					}

					.no-stack.two-up .col {
						width: 50% !important;
					}

					.no-stack .col.num2 {
						width: 16.6% !important;
					}

					.no-stack .col.num3 {
						width: 25% !important;
					}

					.no-stack .col.num4 {
						width: 33% !important;
					}

					.no-stack .col.num5 {
						width: 41.6% !important;
					}

					.no-stack .col.num6 {
						width: 50% !important;
					}

					.no-stack .col.num7 {
						width: 58.3% !important;
					}

					.no-stack .col.num8 {
						width: 66.6% !important;
					}

					.no-stack .col.num9 {
						width: 75% !important;
					}

					.no-stack .col.num10 {
						width: 83.3% !important;
					}

					.video-block {
						max-width: none !important;
					}

					.mobile_hide {
						min-height: 0px;
						max-height: 0px;
						max-width: 0px;
						display: none;
						overflow: hidden;
						font-size: 0px;
					}

					.desktop_hide {
						display: block !important;
						max-height: none !important;
					}
				}
			</style>
		</head>
		<body class="clean-body" style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #ebebeb;">
		<!--[if IE]><div class="ie-browser"><![endif]-->
		<table bgcolor="#ebebeb" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="table-layout: fixed; vertical-align: top; min-width: 320px; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ebebeb; width: 100%;" valign="top" width="100%">
		<tbody>
		<tr style="vertical-align: top;" valign="top">
		<td style="word-break: break-word; vertical-align: top;" valign="top">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color:#ebebeb"><![endif]-->
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #ef233c;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#ef233c;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#ef233c"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#ef233c;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<div></div>
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #ffffff;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#ffffff;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#ffffff"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#ffffff;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:20px; padding-bottom:20px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:20px; padding-bottom:20px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<div></div>
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: transparent;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:transparent;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<div></div>
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #ffffff;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#ffffff;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#ffffff"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#ffffff;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:15px; padding-bottom:30px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:15px; padding-bottom:30px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
		<div style="color:#393d47;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.5;padding-top:10px;padding-right:25px;padding-bottom:10px;padding-left:25px;">
		<div style="line-height: 1.5; font-size: 12px; color: #393d47; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 18px;">
		<p style="line-height: 1.5; word-break: break-word; text-align: left; font-size: 16px; mso-line-height-alt: 24px; margin: 0;"><span style="font-size: 16px;">Hello {$full_name}!</span></p>
		<p style="line-height: 1.5; word-break: break-word; text-align: left; mso-line-height-alt: 18px; margin: 0;"> </p>
		<p style="line-height: 1.5; word-break: break-word; text-align: left; font-size: 16px; mso-line-height-alt: 24px; margin: 0;"><span style="font-size: 16px;">You requested a password reset for your moderator account in Campus Space. Follow the link below to reset your account.</span></p>
		</div>
		</div>
		<!--[if mso]></td></tr></table><![endif]-->
		<div align="center" class="button-container" style="padding-top:35px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
		<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-top: 35px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{$reset_link}" style="height:39pt; width:216.75pt; v-text-anchor:middle;" arcsize="49%" stroke="false" fillcolor="#ef233c"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#ffffff; font-family:Arial, sans-serif; font-size:16px"><![endif]--><a href="{$reset_link}" style="-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #ef233c; border-radius: 25px; -webkit-border-radius: 25px; -moz-border-radius: 25px; width: auto; width: auto; border-top: 0px solid #8a3b8f; border-right: 0px solid #8a3b8f; border-bottom: 0px solid #8a3b8f; border-left: 0px solid #8a3b8f; padding-top: 10px; padding-bottom: 10px; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;" target="_blank"><span style="padding-left:35px;padding-right:35px;font-size:16px;display:inline-block;"><span style="font-size: 16px; line-height: 2; word-break: break-word; mso-line-height-alt: 32px;">RESET PASSWORD</span></span></a>
		<!--[if mso]></center></v:textbox></v:roundrect></td></tr></table><![endif]-->
		</div>
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fafafa;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#fafafa;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#fafafa"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#fafafa;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:60px; padding-bottom:25px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:60px; padding-bottom:25px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: 'Trebuchet MS', Tahoma, sans-serif"><![endif]-->
		<div style="color:#393d47;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
		<div style="line-height: 1.2; font-size: 12px; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; color: #393d47; mso-line-height-alt: 14px;">
		<p style="font-size: 30px; line-height: 1.2; word-break: break-word; text-align: center; font-family: Montserrat, 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; mso-line-height-alt: 36px; margin: 0;"><span style="font-size: 30px;">Clueless?</span></p>
		</div>
		</div>
		<!--[if mso]></td></tr></table><![endif]-->
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #ffffff;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#ffffff;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#ffffff"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#ffffff;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:40px; padding-bottom:40px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:40px; padding-bottom:40px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 20px; padding-left: 20px; padding-top: 20px; padding-bottom: 20px; font-family: Arial, sans-serif"><![endif]-->
		<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;">
		<div style="line-height: 1.2; font-size: 12px; color: #555555; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;">
		<p style="font-size: 16px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 19px; margin: 0;"><span style="font-size: 16px;">We sent you this email because a password reset was requested for the account registered with this email address. If you didn't make such request, please ignore this email, do not click any link!</span></p>
		</div>
		</div>
		<!--[if mso]></td></tr></table><![endif]-->
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<div style="background-color:transparent;">
		<div class="block-grid" style="min-width: 320px; max-width: 680px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #2b2d42;">
		<div style="border-collapse: collapse;display: table;width: 100%;background-color:#2b2d42;">
		<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:680px"><tr class="layout-full-width" style="background-color:#2b2d42"><![endif]-->
		<!--[if (mso)|(IE)]><td align="center" width="680" style="background-color:#2b2d42;width:680px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:20px; padding-bottom:20px;"><![endif]-->
		<div class="col num12" style="min-width: 320px; max-width: 680px; display: table-cell; vertical-align: top; width: 680px;">
		<div class="col_cont" style="width:100% !important;">
		<!--[if (!mso)&(!IE)]><!-->
		<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:20px; padding-bottom:20px; padding-right: 0px; padding-left: 0px;">
		<!--<![endif]-->
		<table cellpadding="0" cellspacing="0" class="social_icons" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top" width="100%">
		<tbody>
		<tr style="vertical-align: top;" valign="top">
		<td style="word-break: break-word; vertical-align: top; padding-top: 15px; padding-right: 15px; padding-bottom: 15px; padding-left: 15px;" valign="top">
		<table align="center" cellpadding="0" cellspacing="0" class="social_table" role="presentation" style="table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-tspace: 0; mso-table-rspace: 0; mso-table-bspace: 0; mso-table-lspace: 0;" valign="top">
		<tbody>
		<tr align="center" style="vertical-align: top; display: inline-block; text-align: center;" valign="top">
		<td style="word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 5px; padding-left: 5px;" valign="top"><a href="https://facebook.com/campusspaceabu" target="_blank"><img alt="Facebook" height="32" src="{$fb_img}" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;" title="facebook" width="32"/></a></td>
		<td style="word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 5px; padding-left: 5px;" valign="top"><a href="https://twitter.com/SpaceAbu" target="_blank"><img alt="Twitter" height="32" src="{$tw_img}" style="text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;" title="twitter" width="32"/></a></td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
		<div style="color:#393d47;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.8;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
		<div style="line-height: 1.8; font-size: 12px; color: #393d47; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 22px;">
		<p style="text-align: center; line-height: 1.8; word-break: break-word; font-size: 14px; mso-line-height-alt: 25px; margin: 0;"><span style="font-size: 14px; color: #8d99ae;">STEM Coders Club, Ahmadu Bello University, Zaria</span></p>
		<p style="text-align: center; line-height: 1.8; word-break: break-word; font-size: 14px; mso-line-height-alt: 25px; margin: 0;"><span style="font-size: 14px; color: #8d99ae;">mail@campus-space.comng   |  (+234) 816 2137 029</span></p>
		<p style="text-align: center; line-height: 1.8; word-break: break-word; mso-line-height-alt: 22px; margin: 0;"> </p>
		<p style="text-align: center; line-height: 1.8; word-break: break-word; mso-line-height-alt: 22px; margin: 0;"><br/><span style="font-size: 14px; color: #8d99ae;">© {$year} Campus Space. All Rights Reserved</span></p>
		</div>
		</div>
		<!--[if mso]></td></tr></table><![endif]-->
		<!--[if (!mso)&(!IE)]><!-->
		</div>
		<!--<![endif]-->
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
		</div>
		</div>
		</div>
		<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
		</td>
		</tr>
		</tbody>
		</table>
		<!--[if (IE)]></div><![endif]-->
		</body>
		</html>
EMAIL;
	}
}