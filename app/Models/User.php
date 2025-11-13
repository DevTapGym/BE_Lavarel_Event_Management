<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Carbon\Carbon;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'is_active',
        'password',
        'phone',
        'roles',
        'email_verified_at',
        'actived_at',
        'current_jti',
        'activation_code',
        'activation_expires_at',
        'last_activation_sent_at',
        'reputation_score',
        'alerts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'is_active' => false,
        'current_jti' => null,
        'activation_code' => null,
        'activation_expires_at' => null,
        'last_activation_sent_at' => null,
        'reputation_score' => 70,
        'avatar' => null,
        'alerts' => [],
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'reputation_score' => 'integer',
    ];

    // --------------------------
    //  ROLE HANDLERS
    // --------------------------

    /**
     * Gán role cho user
     * 
     * @param string $roleName
     * @return void
     */
    public function assignRole($roleName)
    {
        /** @var string $roleName */
        $roles = $this->roles ?? [];
        if (!in_array($roleName, $roles)) {
            $roles[] = $roleName;
            $this->roles = $roles;
            $this->save();
        }
    }

    /**
     * Kiểm tra user có role không
     * 
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        /** @var string $roleName */
        return in_array($roleName, $this->roles ?? []);
    }
    // --------------------------
    //  ALERT HANDLERS
    // --------------------------
    public function addAlert(array $alertData): void
    {
        /** @var array<string, mixed> $alertData */
        $alerts = $this->alerts ?? [];

        $alert = array_merge([
            'is_read' => false,
            'is_deleted' => false,
            'created_at' => Carbon::now()->toDateTimeString(),
        ], $alertData);

        $alerts[] = $alert;
        $this->alerts = $alerts;
        $this->save();
    }


    /**
     * Lấy tất cả alerts đang active (chưa bị xóa)
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getActiveAlerts(): array
    {
        $alerts = $this->alerts ?? [];
        return array_filter($alerts, fn($alert) => empty($alert['is_deleted']) || $alert['is_deleted'] === false);
    }

    /**
     * Kiểm tra xem đã có cảnh báo reputation trong X ngày gần đây chưa
     * 
     * @param string $alertType Loại alert: 'BLOCK_REGISTRATION' hoặc 'WARNING'
     * @param int $days Số ngày kiểm tra (mặc định 30 ngày)
     * @return bool True nếu đã có cảnh báo trong khoảng thời gian
     */
    public function hasRecentReputationAlert(string $alertType, int $days = 30): bool
    {
        /** @var string $alertType */
        /** @var int $days */
        $alerts = $this->alerts ?? [];
        $now = Carbon::now();
        $timeAgo = $now->copy()->subDays($days);

        foreach ($alerts as $alert) {
            // Bỏ qua alert đã bị xóa
            if (!empty($alert['is_deleted']) && $alert['is_deleted'] === true) {
                continue;
            }

            // Kiểm tra loại alert
            if (($alert['type'] ?? '') !== $alertType) {
                continue;
            }

            // Kiểm tra thời gian
            if (!empty($alert['created_at'])) {
                try {
                    $alertTime = Carbon::parse($alert['created_at']);
                    // Nếu alert được tạo sau timeAgo (trong vòng X ngày)
                    if ($alertTime->isAfter($timeAgo)) {
                        return true;
                    }
                } catch (\Exception $e) {
                    // Nếu không parse được thời gian, bỏ qua
                    continue;
                }
            }
        }

        return false;
    }
}
