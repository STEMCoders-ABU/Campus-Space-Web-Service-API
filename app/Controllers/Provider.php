<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Provider extends BaseController
{
    use ResponseTrait;

    public function get_faculties()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $data = $model->getFaculties();
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }

    public function get_departments()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $faculty_id = $this->request->getGet('faculty_id');

        $constraints = [];

        if ($faculty_id)
            $constraints['faculty_id'] = $faculty_id;
            
        $data = $model->getDepartments($constraints);
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }

    public function get_levels()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $data = $model->getLevels();
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }

    public function get_courses()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $department_id = $this->request->getGet('department_id');
        $level_id = $this->request->getGet('level_id');

        $constraints = [];

        if ($department_id)
            $constraints['department_id'] = $department_id;

        if ($level_id)
            $constraints['level_id'] = $level_id;
            
        $data = $model->getCourses($constraints);
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }

    public function get_resource_categories()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $data = $model->getResourceCategories();
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }

    public function get_news_categories()
    {
        $model = \model('App\Models\ProviderModel', true);
        
        $data = $model->getNewsCategories();
		if ($data)
			return $this->respond($data, 200);
		else
			return $this->failNotFound('Data not found!');
    }
}