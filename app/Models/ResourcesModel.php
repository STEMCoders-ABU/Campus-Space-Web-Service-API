<?php namespace App\Models;

use CodeIgniter\Model;

class ResourcesModel extends Model
{
    public function getResources ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('resources')
                            ->where($constraints)
                            ->orderBy('resources.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('resources')
                            ->select('resources.id, resources.course_id, resources.faculty_id, resources.department_id, resources.level_id, ' . 
                                    'resources.category_id, resources.downloads, resources.title, resources.description, resources.file, ' .
                                    'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level, ' .
                                    'resource_categories.category')
                            ->join('courses', 'courses.id = resources.course_id')
                            ->join('faculties', 'faculties.id = resources.faculty_id')
                            ->join('departments', 'departments.id = resources.department_id')
                            ->join('levels', 'levels.id = resources.level_id')
                            ->join('resource_categories', 'resource_categories.id = resources.category_id')
                            ->where($constraints)
                            ->orderBy('resources.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function getResourcesByDownloads ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('resources')
                            ->where($constraints)
                            ->orderBy('resources.downloads DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('resources')
                            ->select('resources.id, resources.course_id, resources.faculty_id, resources.department_id, resources.level_id, ' . 
                                    'resources.category_id, resources.downloads, resources.title, resources.description, resources.file, ' .
                                    'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level, ' .
                                    'resource_categories.category')
                            ->join('courses', 'courses.id = resources.course_id')
                            ->join('faculties', 'faculties.id = resources.faculty_id')
                            ->join('departments', 'departments.id = resources.department_id')
                            ->join('levels', 'levels.id = resources.level_id')
                            ->join('resource_categories', 'resource_categories.id = resources.category_id')
                            ->where($constraints)
                            ->orderBy('resources.downloads DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function getSearchedResources ($constraints, $search, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('resources')
                            ->where($constraints)
                            ->like('resources.title', $search)
                            ->orLike('resources.description', $search)
                            ->orderBy('resources.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('resources')
                            ->select('resources.id, resources.course_id, resources.faculty_id, resources.department_id, resources.level_id, ' . 
                                    'resources.category_id, resources.downloads, resources.title, resources.description, resources.file, ' .
                                    'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level, ' .
                                    'resource_categories.category')
                            ->join('courses', 'courses.id = resources.course_id')
                            ->join('faculties', 'faculties.id = resources.faculty_id')
                            ->join('departments', 'departments.id = resources.department_id')
                            ->join('levels', 'levels.id = resources.level_id')
                            ->join('resource_categories', 'resource_categories.id = resources.category_id')
                            ->where($constraints)
                            ->like('resources.title', $search)
                            ->orLike('resources.description', $search)
                            ->orderBy('resources.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function getCategoryComments ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('category_comments')
                            ->where($constraints)
                            ->orderBy('date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('category_comments')
                            ->select('category_comments.id, category_comments.category_id, category_comments.course_id, category_comments.department_id, ' .
                                    'category_comments.level_id, category_comments.author, category_comments.comment, category_comments.date_added, ' .
                                    'departments.department, levels.level, courses.course_title, courses.course_code, resource_categories.category')
                            ->join('courses', 'courses.id = category_comments.course_id')
                            ->join('departments', 'departments.id = category_comments.department_id')
                            ->join('levels', 'levels.id = category_comments.level_id')
                            ->join('resource_categories', 'resource_categories.id = category_comments.category_id')
                            ->where($constraints)
                            ->orderBy('category_comments.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function getResourceComments ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('comments')
                            ->where($constraints)
                            ->orderBy('date DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('comments')
                            ->select('comments.id, comments.comment, comments.author, comments.date, comments.resource_id, ' .
                                    'resources.course_id, resources.faculty_id, resources.department_id, resources.level_id, ' .
                                    'resources.category_id, resources.downloads, resources.title, resources.description, resources.file, ' .
                                    'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level, ' .
                                    'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level,')
                            ->join('resources', 'resources.id = comments.resource_id')
                            ->join('courses', 'courses.id = resources.course_id')
                            ->join('faculties', 'faculties.id = resources.faculty_id')
                            ->join('departments', 'departments.id = resources.department_id')
                            ->join('levels', 'levels.id = resources.level_id')
                            ->join('resource_categories', 'resource_categories.id = resources.category_id')
                            ->where($constraints)
                            ->orderBy('date DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function add_comment ($entries)
    {
        $this->db->table('comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function add_category_comment ($entries)
    {
        $this->db->table('category_comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function update_resource ($resource_id, $entries)
    {
        return $this->db->table('resources')
            ->where(['id' => $resource_id])
            ->update($entries);
    }

    function get_resource ($resource_id)
    {
        return $this->db->table('resources')
                            ->where(['resources.id' => $resource_id])
                            ->get()->getRowArray();
    }

    public function get_resource_item ($constraints, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('resources')
                        ->where($constraints)
                        ->get()->getRowArray();
        else
            return $this->db->table('resources')
                    ->select('resources.id, resources.course_id, resources.faculty_id, resources.department_id, resources.level_id, ' . 
                            'resources.category_id, resources.downloads, resources.title, resources.description, resources.file, ' .
                            'resources.date_added, courses.course_title, courses.course_code, faculties.faculty, departments.department, levels.level, ' .
                            'resource_categories.category')
                    ->join('courses', 'courses.id = resources.course_id')
                    ->join('faculties', 'faculties.id = resources.faculty_id')
                    ->join('departments', 'departments.id = resources.department_id')
                    ->join('levels', 'levels.id = resources.level_id')
                    ->join('resource_categories', 'resource_categories.id = resources.category_id')
                    ->where($constraints)
                    ->get()->getRowArray();
    }

    public function addSubscription($entries)
    {
        $this->db->table('resources_subscriptions')->insert($entries);
        return $this->insertID();
    }

    public function getSubscription($constraints)
    {
        return $this->db->table('resources_subscriptions')
                    ->where($constraints)
                    ->get()->getRowArray();
    }
}