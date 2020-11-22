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

    public function getModeratorData($constraints)
    {
        return $this->db->table('moderators')
            ->select('moderators.email, moderators.full_name, ' .
                'moderators.gender, moderators.phone, moderators.faculty_id, moderators.department_id, moderators.level_id, ' .
                'moderators.reg_date, faculties.faculty, departments.department, levels.level')
            ->join('faculties', 'faculties.id = moderators.faculty_id')
            ->join('departments', 'departments.id = moderators.department_id')
            ->join('levels', 'levels.id = moderators.level_id')
            ->where($constraints)
            ->get()->getRowArray();
    }

    public function getResourcesCount()
    {
        return $this->db->table('resources')
                        ->select('COUNT(id) AS total')
                        ->get()->getRowArray();
    }

    public function getDepartmentsCount()
    {
        return $this->db->table('departments')
                        ->select('COUNT(id) AS total')
                        ->get()->getRowArray();
    }

    public function getDownloadsCount()
    {
        return $this->db->table('resources')
                        ->select('SUM(downloads) AS total')
                        ->get()->getRowArray();
    }
}