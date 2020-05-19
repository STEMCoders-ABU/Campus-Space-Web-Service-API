<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class News extends BaseController
{
    use ResponseTrait;

    public function show()
    {
        $constraints = [];

        $category_id = $this->request->getGet('category_id');
        $level_id = $this->request->getGet('level_id');
        $department_id = $this->request->getGet('department_id');
        $faculty_id = $this->request->getGet('faculty_id');
        
        if ($level_id)
            $constraints['level_id'] = $level_id;

        if ($department_id)
            $constraints['department_id'] = $department_id;

        if ($faculty_id)
            $constraints['faculty_id'] = $faculty_id;

        if ($category_id)
            $constraints['category_id'] = $category_id;

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getNews($constraints);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No news matching the provided combination was found');
    }

    public function show_category_comments()
    {
        $constraints = [];

        $category_id = $this->request->getGet('category_id');
        $level_id = $this->request->getGet('level_id');
        $department_id = $this->request->getGet('department_id');
        
        if ($level_id)
            $constraints['level_id'] = $level_id;

        if ($department_id)
            $constraints['department_id'] = $department_id;

        if ($category_id)
            $constraints['category_id'] = $category_id;

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getCategoryComments($constraints);
        
        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No category comments matching the provided combination was found');
    }

    public function show_comments()
    {
        $constraints = [];

        $news_id = $this->request->getGet('news_id');

        if ($news_id)
            $constraints['news_id'] = $news_id;

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getNewsComments($constraints);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No news comments matching the provided resource id was found');
    }

    public function add_category_comment()
	{
		$fields = $this->request->getPost();

        if (! $fields)
            return $this->failNotFound('No input data was provided!');

        $validationRules = 
        [
            'author' => 'required|max_length[20]',
            'comment' => 'required|max_length[500]',
            'category_id' => 'required|is_not_unique[news_categories.id]',
            'department_id' => 'required|is_not_unique[departments.id]',
            'level_id' => 'required|is_not_unique[levels.id]',
        ];

        if (! $this->validate($validationRules))
        {
            return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
        }

        $model = \model('App\Models\NewsModel', true);
        $entries =
        [
            'author' => $this->request->getPost('author'),
            'comment' => $this->request->getPost('comment'),
            'category_id' => $this->request->getPost('category_id'),
            'department_id' => $this->request->getPost('department_id'),
            'level_id' => $this->request->getPost('level_id'),
        ];

        if ($model->add_category_comment($entries))
        {
            return $this->respond([], 200);
        }
        else
        {
            return $this->fail('Failed to category add comment!');
        }
    }
    
    public function add_comment()
	{
		$fields = $this->request->getPost();

        if (! $fields)
            return $this->failNotFound('No input data was provided!');

        $validationRules = 
        [
            'author' => 'required|max_length[20]',
            'comment' => 'required|max_length[500]',
            'news_id' => 'required|is_not_unique[news.id]',
        ];

        if (! $this->validate($validationRules))
        {
            return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
        }

        $model = \model('App\Models\NewsModel', true);
        $entries =
        [
            'author' => $this->request->getPost('author'),
            'comment' => $this->request->getPost('comment'),
            'news_id' => $this->request->getPost('news_id'),
        ];

        if ($model->add_comment($entries))
        {
            return $this->respond([], 200);
        }
        else
        {
            return $this->fail('Failed to add comment!');
        }
	}
}