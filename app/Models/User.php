<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get primary role name attribute
     */
    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Tanpa Role';
    }

    /**
     * Check if user has associated transactions or audit records
     */
    public function hasHistoricalData(): bool
    {
        return Reservation::where('created_by', $this->id)->exists()
            || CashierShift::where('user_id', $this->id)->exists()
            || Expense::where('requested_by', $this->id)->orWhere('approved_by', $this->id)->exists()
            || NightAudit::where('performed_by', $this->id)->exists()
            || StockOpname::where('created_by', $this->id)->orWhere('approved_by', $this->id)->exists()
            || AuditLog::where('user_id', $this->id)->exists();
    }
}
