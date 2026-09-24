<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Academy extends Model
{
    use HasFactory, HasMedia, HasSeoMeta;

    protected $fillable = [
        'owner_id','name','slug','code','description','phone','email','address',
        'city','province','website','status',
    ];

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role','status','joined_at'])->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('role','teacher')->withPivot(['status','joined_at']);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('role','student')->withPivot(['status','joined_at']);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('role','parent')->withPivot(['status','joined_at']);
    }

    public function courses(): HasMany { return $this->hasMany(Course::class); }
    public function classrooms(): HasMany { return $this->hasMany(Classroom::class); }
    public function financialTransactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
}