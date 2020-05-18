<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Moderator extends BaseController
{
    use ResponseTrait;
	
	public function index()
	{
		$moderatorsModel = \model('App\Models\ModeratorsModel', true);
		$conditionals =
			[
				'department_id' => 1,
				'level_id' => 1,
			];
		$data = $moderatorsModel->getCourses ($conditionals);
		echo var_dump($data);
	}

	public function show ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
			return $this->respond($moderator_data, 200);
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function update ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$validationRules = [];

			if (isset($fields['email']))
			{
				if ($fields['email'] !== $moderator_data['email'])
					$validationRules['email'] = 'required|max_length[50]|valid_email|is_unique[moderators.email]';
			}
				
			if (isset($fields['password']))
				$validationRules['password'] = 'required|max_length[70]';
			
			if (isset($fields['full_name']))
				$validationRules['full_name'] = 'required|min_length[2]|max_length[50]';
			
			if (isset($fields['gender']))
				$validationRules['gender'] = 'required|min_length[4]|max_length[6]';
			
			if (isset($fields['phone']))
				$validationRules['phone'] = 'required|min_length[11]|max_length[15]';
				
			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$id = $moderator_data['id'];

			if ($moderatorsModel->update($id, $fields))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to update moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function courses ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$conditionals =
			[
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			$courses = $moderatorsModel->getCourses($conditionals);
			if ($courses)
			{
				return $this->respond($courses, 200);
			}
			else
			{
				return $this->failNotFound('No courses are registered for this moderator yet!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function categories ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			
			$categories = $moderatorsModel->getResourceCategories();
			if ($categories)
			{
				return $this->respond($categories, 200);
			}
			else
			{
				return $this->failNotFound('Resource categories not found!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function resource ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$validationRules = [];

			if (isset($fields['email']))
			{
				if ($fields['email'] !== $moderator_data['email'])
					$validationRules['email'] = 'required|max_length[50]|valid_email|is_unique[moderators.email]';
			}
				
			if (isset($fields['password']))
				$validationRules['password'] = 'required|max_length[70]';
			
			if (isset($fields['full_name']))
				$validationRules['full_name'] = 'required|min_length[2]|max_length[50]';
			
			if (isset($fields['gender']))
				$validationRules['gender'] = 'required|min_length[4]|max_length[6]';
			
			if (isset($fields['phone']))
				$validationRules['phone'] = 'required|min_length[11]|max_length[15]';
				
			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$id = $moderator_data['id'];

			if ($moderatorsModel->update($id, $fields))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to update moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	private function authenticate ($request_username)
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

		if ($username !== $request_username)
			return FALSE;

		$moderatorsModel = \model('App\Models\ModeratorsModel', true);

		$data = $moderatorsModel->getModeratorData($username);

		if (! $data)
			return FALSE;

		if ($data['password'] == $password)
			return $data;
		else
			return FALSE;
	}

	private function array_to_string ($array)
	{
		$str = '';
		foreach ($array as $item) 
		{
			$str .= $item . '<br>';
		}

		return $str;
	}
}