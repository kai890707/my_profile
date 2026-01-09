<?php

namespace App\Models;

use CodeIgniter\Model;

class SkillModel extends Model
{
    protected $table            = 'skills';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['category', 'name', 'sort_order'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSkillsByCategory()
    {
        $skills = $this->orderBy('category', 'ASC')
                       ->orderBy('sort_order', 'ASC')
                       ->findAll();

        $grouped = [];
        foreach ($skills as $skill) {
            $grouped[$skill['category']][] = $skill['name'];
        }

        return $grouped;
    }
}
