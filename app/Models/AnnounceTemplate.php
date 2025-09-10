<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnounceTemplate extends Model
{
    use HasFactory;

    protected $primaryKey = 'announce_template_id';

    protected $fillable = [
        'id',
        'branchid',
        'docnumb',
        'cllocal',
        'colocal',
        'paypurpose',
        'clname',
        'coname',
        'sumpay',
        'symid',
        'clacc',
        'coacc',
        'currday',
        'taskkode',
        'cashbox_id',
        'payer_name', // Added the new column
    ];

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class, 'cashbox_id');
    }
}
