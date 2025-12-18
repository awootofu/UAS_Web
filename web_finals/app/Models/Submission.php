<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'submitted_by', 
        'status', 
        'verifier_id', 
        'verified_at'
    ];

    // Relasi ke User (Verifier)
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}