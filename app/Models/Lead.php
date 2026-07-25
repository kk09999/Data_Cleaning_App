<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id',
        'sheet_name',
        'date',
        'month',
        'name',
        'mob',
        'email',
        'raw_course',
        'major_category',
        'is_duplicate'
    ];

    protected $casts = [
        'is_duplicate' => 'boolean',
    ];

    public function importBatch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
