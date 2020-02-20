<?php

namespace Tests\Stubs;

use Cvsouth\EloquentInheritance\InheritableModel;
use Tests\Stubs\Owner;

class Animal extends InheritableModel {
	public $table = 'animals';
	protected $hidden = ['secret'];
	protected $dates = ['born_at'];
	protected $casts = ['male' => 'boolean'];
	protected $appends = ['formatted_born_at'];
	protected $fillable = ['name'];
	protected $guarded = ['species'];

	public function owner() {
		return $this->belongsTo(Owner::class);
	}

	public function getFormattedBornAtAttribute() {
		return $this->born_at->format('H:i d. m. Y');
	}

	public function getAnimalNumberAttribute($value) {
		return number_format((int)$value, 0, '', ' ');
	}
	
}
