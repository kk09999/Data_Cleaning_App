<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'sheet_count',
        'record_count',
        'unique_count',
        'duplicate_count'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'import_batch_id');
    }
}
