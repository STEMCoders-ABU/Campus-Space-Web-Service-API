<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
 
class Moderator extends BaseController
{
    use ResponseTrait;
	
	public function show()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			unset($moderator_data['id']);
			unset($moderator_data['password']);
			return $this->respond($moderator_data, 200);
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function update()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			if (isset($fields['email']))
				$validationRules['email'] = "required|max_length[50]|valid_email|is_unique[moderators.email,email,{$moderator_data['email']}]";
				
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

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$id = $moderator_data['id'];

			if (isset($fields['password']))
				$fields['password'] = password_hash($fields['password'], PASSWORD_DEFAULT);

			if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
				$join = TRUE;
			else
				$join = FALSE;

			if ($moderatorsModel->update($id, $fields))
			{
				$data = $moderatorsModel->getModeratorData($moderator_data['username'], $join);
				return $this->respond($data, 200);
			}
			else
			{
				return $this->fail('Failed to update moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function courses()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
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

	public function add_resource()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
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

			$validationRules = 
			[
				'title' => 'required|max_length[50]|is_unique[resources.title]',
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

	public function add_news()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$validationRules = 
			[
				'title' => 'required|max_length[200]|is_unique[news.title]',
				'content' => 'required|max_length[5000]',
				'category_id' => 'required|is_not_unique[news_categories.id]',
			];

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			$category_id = $this->request->getPost('category_id');
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

	public function get_resources()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
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
			else
			{
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

			if ($data)
			{
				return $this->respond($data, 200);
			}
			else
			{
				return $this->failNotFound('No resources uploaded by this moderator yet!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_news()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
				'news.department_id' => $department_id,
				'news.level_id' => $level_id,
			];

			if ($this->request->getGet('category_id') && is_numeric($this->request->getGet('category_id')))
				$constraints['news.category_id'] = $this->request->getGet('category_id');

			if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
				$join = TRUE;
			else
				$join = FALSE;

			if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
				$offset = $this->request->getGet('offset');
			else
				$offset = 0;

			if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
				$size = $this->request->getGet('size');
			else
				$size = 0;

			$newsModel = \model('App\Models\NewsModel', true);
        	$data = $newsModel->getNews($constraints, $size, $offset, $join);
			if ($data)
			{
				return $this->respond($data, 200);
			}
			else
			{
				return $this->failNotFound('No news uploaded by this moderator yet!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function delete_resource()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$resource_id = $this->request->getGet('resource_id');
			if (! $resource_id)
				return $this->failNotFound('No resource_id was provided!');

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
				'id' => $resource_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			$resourcesModel = \model('App\Models\ResourcesModel', true);
			$resource = $resourcesModel->get_resource_item($constraints);
			if ($moderatorsModel->remove_resource($constraints))
			{
				$file_deleted = unlink(RESOURCES_PATH . $resource['file']);
				if ($file_deleted)
					$file_deleted = 'true';
				else
					$file_deleted = 'false';

				return $this->respond(['file_deleted' => $file_deleted], 200);
			}
			else
			{
				return $this->fail('Failed to delete resource!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function delete_news()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$news_id = $this->request->getGet('news_id');
			if (! $news_id)
				return $this->failNotFound('No news_id was provided!');

			$moderatorsModel = \model('App\Models\ModeratorsModel', true);
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
				'id' => $news_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			if ($moderatorsModel->remove_news($constraints))
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

	public function get_resource()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$resource_id = $this->request->getGet('resource_id');
			if (! $resource_id)
				return $this->failNotFound('No resource_id was provided!');
			
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
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
			if ($data)
			{
				return $this->respond($data, 200);
			}
			else
			{
				return $this->failNotFound('The requested resource does not exist for this moderator!');
			}
		}
		else
			return $this->failUnauthorized('Authentication failed!');
	}

	public function get_news_item()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$news_id = $this->request->getGet('news_id');
			if (! $news_id)
				return $this->failNotFound('No news_id was provided!');

			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];
			$constraints =
			[
				'news.id' => $news_id,
				'news.department_id' => $department_id,
				'news.level_id' => $level_id,
			];

			if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
				$join = TRUE;
			else
				$join = FALSE;

			$newsModel = \model('App\Models\NewsModel', true);
			$data = $newsModel->get_news_item($constraints, $join);
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

	public function update_resource()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$resource_id = $this->request->getGet('resource_id');
			if (! $resource_id)
				return $this->failNotFound('No resource_id was provided!');

			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$constraints =
			[
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

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			if ($resourcesModel->update_resource($resource_id, $fields))
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

	public function update_news()
	{
		$moderator_data = $this->authenticate();
		
		if ($moderator_data)
		{
			$news_id = $this->request->getGet('news_id');
			if (! $news_id)
				return $this->failNotFound('No news_id was provided!');

			$fields = $this->request->getPost();

			if (! $fields)
				return $this->failNotFound('No input data was provided!');

			$faculty_id = $moderator_data['faculty_id'];
			$department_id = $moderator_data['department_id'];
			$level_id = $moderator_data['level_id'];

			$constraints =
			[
				'id' => $news_id,
				'faculty_id' => $faculty_id,
				'department_id' => $department_id,
				'level_id' => $level_id,
			];

			$newsModel = \model('App\Models\NewsModel', true);
			$news = $newsModel->get_news_item($constraints);

			$current_title = $news['title'];
			
			if (isset($fields['title']))
				$validationRules['title'] = "required|max_length[200]|is_unique[news.title,title,{$current_title}]";
				
			if (isset($fields['content']))
				$validationRules['content'] = 'required|max_length[5000]';

			if (isset($fields['category_id']))
				$validationRules['category_id'] = 'required|is_not_unique[news_categories.id]';

			if (! isset($validationRules))
				return $this->failNotFound('No valid input data was provided!');

			if (! $this->validate($validationRules))
			{
				return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
			}

			if ($newsModel->update_news($fields, $constraints))
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

	public function add_course()
	{
		$moderator_data = $this->authenticate();
		
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
}