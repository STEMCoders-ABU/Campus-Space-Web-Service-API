<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Resources extends BaseController
{
    use ResponseTrait;

    public function show()
    {
        $constraints = [];

        $course_id = $this->request->getGet('course_id');
        $category_id = $this->request->getGet('category_id');
        $level_id = $this->request->getGet('level_id');
        $department_id = $this->request->getGet('department_id');
        $faculty_id = $this->request->getGet('faculty_id');
        
        if ($level_id)
            $constraints['resources.level_id'] = $level_id;

        if ($department_id)
            $constraints['resources.department_id'] = $department_id;

        if ($faculty_id)
            $constraints['resources.faculty_id'] = $faculty_id;

        if ($category_id)
            $constraints['resources.category_id'] = $category_id;

        if ($course_id)
            $constraints['resources.course_id'] = $course_id;

        if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
            $join = FALSE;

        if ($this->request->getGet('order_by_downloads') && $this->request->getGet('order_by_downloads') == 'true')
            $by_downloads = TRUE;
        else
            $by_downloads = FALSE;

        if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
            $size = $this->request->getGet('size');
        else
        {
            if ($by_downloads)
                $size = 10;
            else
                $size = 0;
        }

        if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
            $offset = $this->request->getGet('offset');
        else
            $offset = 0;

        $resourcesModel = \model('App\Models\ResourcesModel', true);

        if ($by_downloads)
            $data = $resourcesModel->getResourcesByDownloads($constraints, $size, $offset, $join);
        else
            $data = $resourcesModel->getResources($constraints, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No resource matching the provided combination was found');
    }

    public function get_resource()
    {
        $resource_id = $this->request->getGet('resource_id');
        if (! $resource_id)
            return $this->failNotFound('No resource_id was provided!');

        if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
            $join = FALSE;

        $resourcesModel = \model('App\Models\ResourcesModel', true);
        $data = $resourcesModel->get_resource_item(['resources.id' => $resource_id], $join);
        if ($data)
        {
            return $this->respond($data, 200);
        }
        else
        {
            return $this->failNotFound('The requested resource does not exist!');
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
            $constraints['resources.level_id'] = $level_id;

        if ($department_id)
            $constraints['resources.department_id'] = $department_id;

        if ($faculty_id)
            $constraints['resources.faculty_id'] = $faculty_id;

        if ($category_id)
            $constraints['resources.category_id'] = $category_id;

        if ($course_id)
            $constraints['resources.course_id'] = $course_id;

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

        $resourcesModel = \model('App\Models\ResourcesModel', true);
        $data = $resourcesModel->getSearchedResources($constraints, $search, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No resource matching the provided combination was found');
    }

    public function show_category_comments()
    {
        $constraints = [];

        $course_id = $this->request->getGet('course_id');
        $category_id = $this->request->getGet('category_id');
        $level_id = $this->request->getGet('level_id');
        $department_id = $this->request->getGet('department_id');
        
        if ($level_id)
            $constraints['category_comments.level_id'] = $level_id;

        if ($department_id)
            $constraints['category_comments.department_id'] = $department_id;

        if ($category_id)
            $constraints['category_comments.category_id'] = $category_id;

        if ($course_id)
            $constraints['category_comments.course_id'] = $course_id;

        if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
            $size = $this->request->getGet('size');
        else
            $size = 0;

        if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
            $offset = $this->request->getGet('offset');
        else
            $offset = 0;

        if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
            $join = FALSE;

        $resourcesModel = \model('App\Models\ResourcesModel', true);
        $data = $resourcesModel->getCategoryComments($constraints, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No category comments matching the provided combination was found');
    }

    public function show_comments()
    {
        $constraints = [];

        $resource_id = $this->request->getGet('resource_id');

        if ($resource_id)
            $constraints['resource_id'] = $resource_id;

        if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
            $size = $this->request->getGet('size');
        else
            $size = 0;

        if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
            $offset = $this->request->getGet('offset');
        else
            $offset = 0;

        if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
            $join = TRUE;
        else
            $join = FALSE;

        $resourcesModel = \model('App\Models\ResourcesModel', true);
        $data = $resourcesModel->getResourceComments($constraints, $size, $offset, $join);

        if ($data)
            return $this->respond($data, 200);
        else
            return $this->failNotFound('No resource comments matching the provided resource id was found');
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
            'category_id' => 'required|is_not_unique[resource_categories.id]',
            'course_id' => 'required|is_not_unique[courses.id]',
            'department_id' => 'required|is_not_unique[departments.id]',
            'level_id' => 'required|is_not_unique[levels.id]',
        ];

        if (! $this->validate($validationRules))
        {
            return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
        }

        $model = \model('App\Models\ResourcesModel', true);
        $entries =
        [
            'author' => $this->request->getPost('author'),
            'comment' => $this->request->getPost('comment'),
            'category_id' => $this->request->getPost('category_id'),
            'course_id' => $this->request->getPost('course_id'),
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
            'resource_id' => 'required|is_not_unique[resources.id]',
        ];

        if (! $this->validate($validationRules))
        {
            return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
        }

        $model = \model('App\Models\ResourcesModel', true);
        $entries =
        [
            'author' => $this->request->getPost('author'),
            'comment' => $this->request->getPost('comment'),
            'resource_id' => $this->request->getPost('resource_id'),
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
    
    public function download()
    {
        if ($this->request->getGet('resource_id') && is_numeric($this->request->getGet('resource_id')))
            $resource_id = $this->request->getGet('resource_id');
        else
            return $this->failNotFound('No input data was provided!');

        $resources_model = \model('App\Models\ResourcesModel', true);
        $resource = $resources_model->get_resource($resource_id);
        
        if ($resource)
        {
            $file = RESOURCES_PATH . $resource['file'];
            
            if (file_exists($file))
            {
                $entry = ['downloads' => $resource['downloads'] + 1];
                $resources_model->update_resource($resource_id, $entry);
                return $this->response->download($file, NULL, TRUE);
            }
            else
            {
                return $this->failNotFound('The requested resource file does not exist!');
            }
        }
        else
        {
            return $this->failNotFound('The requested resource does not exist!');
        }
    }
}