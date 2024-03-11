<?php

namespace App\Models\Code;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeQueryLogModel extends Model
{
    use HasFactory;

    protected $table = "code_query_log";
    protected $primaryKey = "id";

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $dateFormat = 'U';

    protected $fillable = [
        "id",
        "code_id",
        "ip",
        "created_at",
       ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];





}
