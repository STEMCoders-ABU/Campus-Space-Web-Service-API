<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Moderator extends BaseController
{
    use ResponseTrait;
	
	public function index()
	{
		$moderatorsModel = \model('App\Models\ModeratorsModel', true);
		echo $moderatorsModel->get_resource_title(1);
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

	public function add_resource ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$allowed_types = '';
			$category_id = $this->request->getPost('category_id');
			$course_id = $this->request->getPost('course_id');
			
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
				$file_size = 50024;
			else if ($category_id == 2)
				$file_size = 1500024;
			else if ($category_id == 3)
				$file_size = 50024;
			else if ($category_id == 4)
				$file_size = 50024;

			$validationRules = 
			[
				'title' => 'required|min_length[2]|max_length[50]|is_unique[resources.title]',
				'description' => 'required|max_length[2000]',
				'category_id' => 'required|is_not_unique[resource_categories.id]',
				'course_id' => 'required|is_not_unique[courses.id]',
				'file' => "uploaded[file]|max_size[file,{$file_size}]|ext_in[file,{$allowed_types}]",
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$file = $this->request->getFile('file');
			$file_name = strtolower(\url_title( $this->request->getPost('title'))) . '.' . $file->getExtension();

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$entries =
			[
				'title' => $this->request->getPost('title'),
				'description' => $this->request->getPost('description'),
				'course_id' => $course_id,
				'category_id' => $category_id,
				'faculty_id' => $faculty_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
				'file' => $file_name,
			];

			if ($moderatorsModel->add_resource($entries))
			{
				$file->move(RESOURCES_PATH, $file_name);
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to add resource!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function add_news ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$validationRules = 
			[
				'title' => 'required|min_length[2]|max_length[200]|is_unique[news.title]',
				'content' => 'required|max_length[5000]',
				'category_id' => 'required|is_not_unique[news_categories.id]',
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$category_id = $this->request->getPost('category_id');
			$course_id = $this->request->getPost('course_id');
			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$entries =
			[
				'title' => $this->request->getPost('title'),
				'content' => $this->request->getPost('content'),
				'category_id' => $category_id,
				'faculty_id' => $faculty_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->add_news($entries))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to add news!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_resources ($username)
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

			$resources = $moderatorsModel->getResources($conditionals);
			if ($resources)
			{
				return $this->respond($resources, 200);
			}
			else
			{
				return $this->failNotFound('No resources uploaded by this moderator yet!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_news ($username)
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

			$news = $moderatorsModel->getNews($conditionals);
			if ($news)
			{
				return $this->respond($news, 200);
			}
			else
			{
				return $this->failNotFound('No news uploaded by this moderator yet!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function delete_resource ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$conditionals =
			[
				'id' => $id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->remove_resource($conditionals))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to delete resource!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function delete_news ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$conditionals =
			[
				'id' => $id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->remove_news($conditionals))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to delete news!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_resource ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$conditionals =
			[
				'id' => $id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			$data = $moderatorsModel->get_resource($conditionals);
			if ($data)
			{
				return $this->respond($data, 200);
			}
			else
			{
				return $this->failNotFound('Resource does not exist for this moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_news_item ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$conditionals =
			[
				'id' => $id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			$data = $moderatorsModel->get_news_item($conditionals);
			if ($data)
			{
				return $this->respond($data, 200);
			}
			else
			{
				return $this->failNotFound('News does not exist for this moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function update_resource ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$current_title = $moderatorsModel->get_resource_title($id);

			$validationRules = 
			[
				'title' => "required|min_length[2]|max_length[50]|is_unique[resources.title,title,{$current_title}]",
				'description' => 'required|max_length[2000]',
				'course_id' => 'required|is_not_unique[courses.id]',
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$entries =
			[
				'title' => $this->request->getPost('title'),
				'description' => $this->request->getPost('description'),
				'course_id' => $this->request->getPost('course_id'),
			];

			$conditionals =
			[
				'id' => $id,
				'faculty_id' => $faculty_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->update_resource($entries, $conditionals))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to update resource!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function update_news ($username, $id)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$current_title = $moderatorsModel->get_news_title($id);

			$validationRules = 
			[
				'title' => "required|min_length[2]|max_length[200]|is_unique[news.title,title,{$current_title}]",
				'content' => 'required|max_length[5000]',
				'category_id' => 'required|is_not_unique[news_categories.id]',
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$entries =
			[
				'title' => $this->request->getPost('title'),
				'content' => $this->request->getPost('content'),
				'category_id' => $this->request->getPost('category_id'),
			];

			$conditionals =
			[
				'id' => $id,
				'faculty_id' => $faculty_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->update_news($entries, $conditionals))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to update news!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function add_course ($username)
	{
		$moderator_data = $this->authenticate($username);
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$validationRules = 
			[
				'course_title' => 'required|max_length[60]|is_unique[courses.course_title]',
				'course_code' => 'required|max_length[12]|is_unique[courses.course_code]',
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$entries =
			[
				'course_title' => $this->request->getPost('course_title'),
				'course_code' => $this->request->getPost('course_code'),
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->add_course($entries))
			{
				return $this->respond([], 200);
			}
			else
			{
				return $this->fail('Failed to add course!');
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
}