<?php

namespace App\Models\Code;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeModel extends Model
{
    use HasFactory;

    protected $table = "code";
    protected $primaryKey = "id";


    protected $fillable = [
        "id",
        "code",
        "batch",
        "created_at",
       ];





}
