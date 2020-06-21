<?php namespace App\Models;

use CodeIgniter\Model;

class ProviderModel extends Model
{
    public function getCourses ($constraints)
    {
        return $this->db->table('courses')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function getDepartments ($constraints)
    {
        return $this->db->table('departments')
                        ->where($constraints)
                        ->orderBy('department')
                        ->get()->getResultArray();
    }

    public function getFaculties()
    {
        return $this->db->table('faculties')
                        ->orderBy('faculty')
                        ->get()->getResultArray();
    }

    public function getLevels()
    {
        return $this->db->table('levels')
                        ->orderBy('level')
                        ->get()->getResultArray();
    }

    public function getResourceCategories()
    {
        return $this->db->table('resource_categories')
                        ->orderBy('category')
                        ->get()->getResultArray();
    }

    public function getNewsCategories()
    {
        return $this->db->table('news_categories')
                        ->orderBy('category')
                        ->get()->getResultArray();
    }
}