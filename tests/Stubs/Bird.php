<?php

namespace Tests\Stubs;

use Tests\Stubs\Animal;
use Tests\Stubs\Trainer;

class Bird extends Animal {
	public $table = 'birds';
	protected $hidden = ['price'];
	protected $dates = ['acquired_at'];
	protected $casts = ['flying' => 'boolean'];
	protected $appends = ['formatted_acquired_at'];
	protected $fillable = ['flying'];
	protected $guarded = ['wingspan'];

	public function trainer() {
		return $this->belongsTo(Trainer::class);
	}

	public function getFormattedAcquiredAtAttribute() {
		return $this->acquired_at->format('H:i d. m. Y');
	}

	public function getBirdNumberAttribute($value) {
		return number_format((int)$value, 0, '', ' ');
	}

}
