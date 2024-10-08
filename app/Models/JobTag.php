<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTag extends Model
{
    use HasFactory;

    protected $table = 'job_tag';

    protected $fillable = ['job_id', 'tag_id'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
