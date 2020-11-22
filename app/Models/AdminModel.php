<?php namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    public function getAdminData($username) 
    {
        return $this->db->table('administrators')
            ->where('username', $username)
            ->get()->getRowArray();
    }

    public function getAdminRaw($username) 
    {
        return $this->db->table('administrators')
            ->where('username', $username)
            ->get()->getRowArray();
    }

    public function getAdminPassword($username)
    {
        return $this->db->table('administrators')
            ->select('password')
            ->where('username', $username)
            ->get()->getRowArray()['password'];
    }

    public function addFaculty($entries)
    {
        if ($this->db->table('faculties')->insert($entries))
            return $this->insertID();
        else
            return FALSE;
    }

    public function getFaculty($id) 
    {
        return $this->db->table('faculties')
            ->where('id', $id)
            ->get()->getRowArray();
    }

    public function updateFaculty($entries, $id)
    {
        return $this->db->table('faculties')
            ->where('id', $id)
            ->update($entries);
    }

    public function removeFaculty($id)
    {
        return $this->db->table('faculties')
            ->where('id', $id)
            ->delete();
    }

    public function addDepartment($entries)
    {
        if ($this->db->table('departments')->insert($entries))
            return $this->insertID();
        else
            return FALSE;
    }

    public function getDepartment($id) 
    {
        return $this->db->table('departments')
            ->where('id', $id)
            ->get()->getRowArray();
    }

    public function updateDepartment($entries, $id)
    {
        return $this->db->table('departments')
            ->where('id', $id)
            ->update($entries);
    }

    public function removeDepartment($id)
    {
        return $this->db->table('departments')
            ->where('id', $id)
            ->delete();
    }

    public function getModerator($constraints) 
    {
        return $this->db->table('moderators')
            ->where($constraints)
            ->get()->getRowArray();
    }

    public function addModerator($entries)
    {
        if ($this->db->table('moderators')->insert($entries))
            return $this->insertID();
        else
            return FALSE;
    }
}