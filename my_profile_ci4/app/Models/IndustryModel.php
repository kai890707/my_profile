<?php

namespace App\Models;

use CodeIgniter\Model;

class IndustryModel extends Model
{
    protected $table = 'industries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'slug',
        'description',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[industries.name,id,{id}]',
        'slug' => 'required|alpha_dash|is_unique[industries.slug,id,{id}]',
    ];
}
