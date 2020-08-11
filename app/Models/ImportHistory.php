<?php

namespace App\Models;

use App\Helpers\Enum\ImportStatus;
use App\Models\Playbooks\Sequence;
use Auth;
use Illuminate\Database\Query\Builder;
use Salestools\Enumerable\ObjectType;
use Salestools\Support\Traits\JsonAccess;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * App\Models\ImportHistory
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $team_id
 * @property integer $object_type
 * @property integer $import_type
 * @property integer $status
 * @property array $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * Magic methods
 * @method static Builder|ImportHistory whereId($value)
 * @method static Builder|ImportHistory whereUserId($value)
 * @method static Builder|ImportHistory whereTeamId($value)
 * @method static Builder|ImportHistory whereImportType($value)
 * @method static Builder|ImportHistory whereObjectType($value)
 * @method static Builder|ImportHistory whereStatus($value)
 * @method static Builder|ImportHistory whereData($value)
 * @method static Builder|ImportHistory whereCreatedAt($value)
 * @method static Builder|ImportHistory whereUpdatedAt($value)
 * @method static Builder|ImportHistory whereDeletedAt($value)
 * @mixin \Eloquent
 *
 * scopes
 * @method static Builder|ImportHistory allowed()
 *
 * relations
 * @property-read User $user
 *
 * mapping
 * @property-read File $file
 */
class ImportHistory extends BaseModel
{
    use JsonAccess;

    protected $table = 'import_history';
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return null|File
     */
    public function getFileAttribute()
    {
        try {
            return new File(storage_path($this->getData('file_path')));
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    /**
     * Database connection name
     *
     * @var string
     */
    protected $connection = 'common';

    /**
     * User relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Scope a query to only include the appropriate public.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllowed($query)
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Set an array item to a given value
     *
     * @param $key
     * @param $value
     */
    public function setData($key, $value)
    {
        $this->setJson($key, $value, 'data');
    }

    /**
     * Get an item from an array
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        return $this->getJson($key, 'data', $default);
    }

    /**
     * @return Sequence|null
     */
    public function getSequence()
    {
        $sequenceId = $this->getData('sequence_id');

        if ($this->isProspectType() && $sequenceId) {
            return Sequence::find($sequenceId);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function finish()
    {
        $this->status = ImportStatus::FINISHED;
        return $this->save();
    }

    /**
     * @return bool
     */
    public function isProspectType()
    {
        return $this->object_type == ObjectType::PROSPECT;
    }

    /**
     * @param \App\Models\User $user
     *
     * @return string
     */
    public static function directory(User $user)
    {
        return 'app/imports/' . $user->id . '/' . date('Y_m_d') . '/';
    }

}
