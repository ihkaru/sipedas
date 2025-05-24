<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, "email", "email");
    }
    public function updatePassword($passwordLama, $passwordBaru, bool $checkRole = true)
    {
        if ($this->canGantiPassword($checkRole) || Hash::check($passwordLama, $this->password)) {
            $this->update([
                'password' => Hash::make($passwordBaru)
            ]);
            return true;
        }
        return null;
    }
    public function canGantiPassword(bool $checkRole = true)
    {
        if (!$checkRole) return true;
        return $this->hasRole('super_admin') || $this->hasRole('kepala_satker');
    }
    public static function getPpspm($withPegawai = true)
    {
        if ($withPegawai) return self::role('ppspm')->with('pegawai')->get();
        return self::role('ppspm')->get();
    }
    public static function getBendahara($withPegawai = true)
    {
        if ($withPegawai) return self::role('bendahara')->with('pegawai')->get();
        return self::role('bendahara')->get();
    }
    public static function getPpk($withPegawai = true)
    {
        if ($withPegawai) return self::role('ppk')->with('pegawai')->get();
        return self::role('ppk')->get();
    }
    public static function getTestPegawai($withPegawai = true)
    {
        if ($withPegawai) return self::where("email", "ihzakarunia@bps.go.id")->with('pegawai')->get();
        return self::where("email", "ihzakarunia@bps.go.id")->get();
    }
}
