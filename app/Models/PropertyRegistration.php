<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use App\Models\{Zone,Source};

class PropertyRegistration extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['zone_id','property_type_id','property_no','old_property_no','property_name','property_address','mauja','sheet_no','plot_no','area','ready_reckoner_rate','valuation','source_id','development_status','date_of_acquisition','remark','latitude','longitude','property_photo_upload','dp_plan_upload','unit_type'];

    public function zone()
    {
        return $this->belongsTo(Zone::class)->withDefault();
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function propertyUnit()
    {
        return $this->hasMany(PropertyUnit::class,'property_reg_id');
    }

    public static function booted()
    {
        static::created(function (PropertyRegistration $propertyregistration)
        {
            self::where('id', $propertyregistration->id)->update([
                'created_by'=> Auth::user()->id,
            ]);
        });
        static::updated(function (PropertyRegistration $propertyregistration)
        {
            self::where('id', $propertyregistration->id)->update([
                'updated_by'=> Auth::user()->id,
            ]);
        });
        static::deleted(function (PropertyRegistration $propertyregistration)
        {
            self::where('id', $propertyregistration->id)->update([
                'deleted_by'=> Auth::user()->id,
            ]);
        });
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getUser = self::orderBy('property_no', 'desc')->first();

            if ($getUser) {
                $latestID = intval(substr($getUser->property_no, 5));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }
            $model->property_no = 'BNCMC_' . sprintf("%05s", $nextID);
            while (self::where('property_no', $model->property_no)->exists()) {
                $nextID++;
                $model->property_no = 'BNCMC_' . sprintf("%05s", $nextID);
            }
        });
    }
}
