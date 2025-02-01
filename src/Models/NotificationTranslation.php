<?php

namespace MhdElawi\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTranslation extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = config('notification.table_names.notification_translations') ?: parent::getTable();
    }

    protected $fillable = ['title', 'body'];
    public $timestamps = false;


}
