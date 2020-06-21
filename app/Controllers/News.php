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
            $constraints['news.level_id'] = $level_id;

        if ($department_id)
            $constraints['news.department_id'] = $department_id;

        if ($faculty_id)
            $constraints['news.faculty_id'] = $faculty_id;

        if ($category_id)
            $constraints['news.category_id'] = $category_id;

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

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getNews($constraints, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No news matching the provided combination was found');
    }

    public function get_news_item()
    {
        $news_id = $this->request->getGet('news_id');
        if (! $news_id)
            return $this->failNotFound('No news_id was provided!');

        if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
            $join = FALSE;

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->get_news_item(['news.id' => $news_id], $join);
        if ($data)
        {
            return $this->respond($data, 200);
        }
        else
        {
            return $this->failNotFound('The requested news does not exist!');
        }
    }

    public function search()
    {
        $constraints = [];

        $search = $this->request->getPost('search');
        if (! $search)
            return $this->failNotFound('No input data was provided!');

        $course_id = $this->request->getGet('course_id');
        $category_id = $this->request->getGet('category_id');
        $level_id = $this->request->getGet('level_id');
        $department_id = $this->request->getGet('department_id');
        $faculty_id = $this->request->getGet('faculty_id');
        
        if ($level_id)
            $constraints['news.level_id'] = $level_id;

        if ($department_id)
            $constraints['news.department_id'] = $department_id;

        if ($faculty_id)
            $constraints['news.faculty_id'] = $faculty_id;

        if ($category_id)
            $constraints['news.category_id'] = $category_id;

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

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getSearchedNews($constraints, $search, $size, $offset, $join);

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
            $constraints['news_category_comments.level_id'] = $level_id;

        if ($department_id)
            $constraints['news_category_comments.department_id'] = $department_id;

        if ($category_id)
            $constraints['news_category_comments.category_id'] = $category_id;

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

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getCategoryComments($constraints, $size, $offset, $join);
        
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

        $newsModel = \model('App\Models\NewsModel', true);
        $data = $newsModel->getNewsComments($constraints, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No news comments matching the provided news id was found');
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