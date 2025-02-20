<?php

namespace MhdElawi\Notification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Notification extends Model implements TranslatableContract
{
    use Translatable;

    public function __construct()
    {
        parent::__construct();
        $this->table = config('notification.table_names.notifications') ?: parent::getTable();
    }

    // Attributes that are translatable (used for multi-language support)
    public array $translatedAttributes = ['title', 'body'];

    // Fillable fields for mass assignment
    protected $fillable = [
        'model_id',
        'model_type',
        'related_id',
        'related_type',
        'extra_fields' ,
        'icon' ,
        'seen_at',
    ];

    protected $hidden = ['updated_at'];

    protected $casts = [
        'extra_fields' => 'array'
    ];


    /**
     * Relationship to the user (MorphTo)
     *
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship to the related model (MorphTo)
     *
     * @return MorphTo
     */
    public function relatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get notifications for a specific user
     *
     * @param Builder $query
     * @param mixed $user
     * @return Builder
     */
    public function scopeFor(Builder $query, mixed $user): Builder
    {
        return $query->where('model_type', get_class($user))
            ->where('model_id', $user->id);
    }

    /**
     * Get the translated attributes for this model
     *
     * @return array
     */
    public function translatedAttributes(): array
    {
        return ['title', 'body'];
    }

}
