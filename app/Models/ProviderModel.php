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
                        ->get()->getResultArray();
    }

    public function getFaculties()
    {
        return $this->db->table('faculties')
                        ->get()->getResultArray();
    }

    public function getLevels()
    {
        return $this->db->table('levels')
                        ->get()->getResultArray();
    }
}