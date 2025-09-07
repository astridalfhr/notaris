<?php
// app/Models/WorkFileModel.php

namespace App\Models;

use CodeIgniter\Model;

class WorkFileModel extends Model
{
    protected $table = 'work_files';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'category',     // 'PPAT' | 'Notaris'
        'subtype',      // e.g. 'AJB', 'Hibah', 'PT', etc.
        'title',
        'notes',
        'filename',     // stored file name on disk
        'mime',
        'size',
        'uploaded_by',  // optional (user name/email)
        'employees_id', // optional linkage if needed
    ];
}
